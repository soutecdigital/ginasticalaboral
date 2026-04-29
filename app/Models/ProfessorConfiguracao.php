<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: ProfessorConfiguracao
 * Objetivo: Armazenar o histórico de valores de aula e reajustes (Normal, Online e Avulso).
 * LEGENDA: Permite que o sistema saiba quanto pagar por aula em diferentes períodos e modalidades.
 */
class ProfessorConfiguracao extends Model
{
    // LEGENDA: Define o nome exato da tabela criada no SQL
    protected $table = 'professor_configuracoes';

    // LEGENDA: Campos permitidos para preenchimento (Mass Assignment)
    protected $fillable = [
        'user_id', 
        'valor_aula', 
        'valor_aula_online', 
        'valor_aula_avulso', 
        'data_inicio_vigencia', 
        'observacao'
    ];

    /**
     * LEGENDA: Casts de Dados (Poka-Yoke)
     * Garante que os valores venham como decimais e a data como objeto Carbon.
     */
    protected $casts = [
        'valor_aula'            => 'decimal:2',
        'valor_aula_online'     => 'decimal:2',
        'valor_aula_avulso'     => 'decimal:2',
        'data_inicio_vigencia'  => 'date',
    ];

    /**
     * RELACIONAMENTO: Pertence a um Usuário
     * LEGENDA: Liga a configuração de valor de volta ao Professor.
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}