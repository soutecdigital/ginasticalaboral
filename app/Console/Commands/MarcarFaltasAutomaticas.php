<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\User;
use App\Models\Presenca;
use Illuminate\Support\Facades\DB;

class MarcarFaltasAutomaticas extends Command
{
    // O comando que você digita no terminal se quiser testar manualmente
    protected $signature = 'laboral:marcar-faltas';

    // Descrição do que o robô faz
    protected $description = 'Verifica aulas não registradas ontem e marca falta automática para os alunos.';

    public function handle()
    {
        $ontem = now()->subDay();
        // Converte a data de ontem para o nome do campo no banco (ex: 'seg', 'ter')
        $diaSemana = strtolower($ontem->format('D')); 
        // Pequeno ajuste de tradução de 'Sun-Sat' para seus campos 'seg-sab'
        $mapaDias = ['mon'=>'seg', 'tue'=>'ter', 'wed'=>'qua', 'thu'=>'qui', 'fri'=>'sex', 'sat'=>'sab'];
        
        if (!array_key_exists($diaSemana, $mapaDias)) return; // Domingo não tem aula
        
        $campoDia = $mapaDias[$diaSemana];

        // 1. Buscar empresas que tinham aula programada ontem
        $empresas = Empresa::where($campoDia, 1)->where('ativo', 1)->get();

        $contador = 0;

        foreach ($empresas as $empresa) {
            // 2. Buscar alunos vinculados a essa empresa
            $alunos = $empresa->users()->where('perfil', 'aluno')->get();

            foreach ($alunos as $aluno) {
                // 3. Verificar se já existe registro (Presença, Falta ou Justificativa)
                $registroExistente = Presenca::where('aluno_id', $aluno->id)
                    ->where('empresa_id', $empresa->id)
                    ->where('data_presenca', $ontem->toDateString())
                    ->exists();

                if (!$registroExistente) {
                    // 4. Se não existe, o sistema cria a FALTA AUTOMÁTICA
                    $novaPresenca = Presenca::create([
                        'aluno_id'      => $aluno->id,
                        'empresa_id'    => $empresa->id,
                        'data_presenca' => $ontem->toDateString(),
                        'hora_presenca' => '23:59:59',
                        'status'        => 'F', // Falta
                        'professor_id'  => 1,   // ID do sistema/admin para auditoria
                        'user_id'       => 1,
                        'observacao'    => 'Falta automática: Aula não registrada no dia programado.'
                    ]);

                    // 5. Grava na sua tabela de auditoria para os sócios saberem
                    DB::table('presenca_auditoria')->insert([
                        'presenca_id'     => $novaPresenca->id,
                        'professor_id'    => 1,
                        'status_anterior' => 'Pendente',
                        'status_novo'     => 'F',
                        'motivo'          => 'Processamento automático por ausência de registro.',
                        'created_at'      => now()
                    ]);
                    $contador++;
                }
            }
        }

        $this->info("Robô finalizado! {$contador} faltas automáticas foram registradas.");
    }
}