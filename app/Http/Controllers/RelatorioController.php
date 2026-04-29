<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Escala; // Certifique-se de que o Model Escala existe
use Carbon\Carbon;

class RelatorioController extends Controller
{
   public function escalasCanceladas()
{
    // Carregamos a escala junto com o professor e a empresa vinculada
    $cancelamentos = Escala::with(['professor', 'empresa', 'usuarioCancelamento']) 
        ->where('status_cancelamento', 'cancelado')
        ->orderBy('data_cancelamento', 'desc')
        ->paginate(20);

    return view('relatorios.escalas_canceladas', compact('cancelamentos'));
}
}