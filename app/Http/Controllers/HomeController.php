<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escala; // Importante para reconhecer a tabela de escalas
use App\Models\EmpresaUserPresenca;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    /**
     * Realiza o check-in do professor
     */
    public function postCheckin(Request $request, $id)
    {
        // Busca a escala e a empresa relacionada
        $escala = Escala::with('empresa')->findOrFail($id);

        $latUsuario = $request->latitude;
        $lonUsuario = $request->longitude;
        
        $latEmpresa = $escala->empresa->latitude;
        $lonEmpresa = $escala->empresa->longitude;

        // Se a empresa não tiver coordenadas, pula a validação de distância
        if (!$latEmpresa || !$lonEmpresa) {
            $this->confirmarPresenca($escala);
            return response()->json([
                'success' => true,
                'message' => 'Check-in realizado (Unidade sem coordenadas para validar).'
            ]);
        }

        $distancia = $this->calcularDistancia($latUsuario, $lonUsuario, $latEmpresa, $lonEmpresa);

        // Raio de 500 metros (0.5 km)
        if ($distancia <= 0.5) {
            $this->confirmarPresenca($escala);

            return response()->json([
                'success' => true,
                'message' => 'Check-in realizado com sucesso!'
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => "Você está a " . round($distancia * 1000) . "m da unidade. Aproxime-se mais."
        ], 400);
    }

    private function confirmarPresenca($escala)
    {
        // 1️⃣ UPDATE na tabela escala (já funcionando)
        $escala->update([
            'checkin' => Carbon::now(),
            'status_presenca' => 'confirmada'
        ]);

        // 2️⃣ CONDIÇÃO ADICIONAL: Pega os alunos vinculados à empresa
        try {
            // Busca todos os usuários (alunos) vinculados a esta empresa
            $alunosEmpresa = DB::table('empresa_user')
                ->where('empresa_id', $escala->empresa_id)
                ->pluck('user_id');

            if ($alunosEmpresa->isNotEmpty()) {
                // 3️⃣ Para cada aluno, cria um registro em empresa_user_presenca
                $registrosPresenca = $alunosEmpresa->map(function ($alunoId) use ($escala) {
                    return [
                        'empresa_id' => $escala->empresa_id,
                        'user_id' => $alunoId,
                        'professor_id' => $escala->user_id, // Professor que confirmou presença
                        'presenca' => 0, // 0 = pendente de confirmação do aluno
                        'ativo' => true,
                        'created_at' => Carbon::now()
                    ];
                });

                // 4️⃣ INSERT em lote
                EmpresaUserPresenca::insert($registrosPresenca->toArray());
            }
        } catch (\Exception $e) {
            // Log do erro mas não impede que o check-in seja confirmado
            Log::error('Erro ao registrar presença em empresa_user_presenca: ' . $e->getMessage());
        }

        return true;
    }

    private function calcularDistancia($lat1, $lon1, $lat2, $lon2) 
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
}