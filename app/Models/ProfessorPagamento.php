<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: ProfessorPagamento
 * Objetivo: Espelho financeiro das escalas confirmadas (histórico de pagamentos a professores).
 * LEGENDA: Registra cada pagamento realizado ao professor conforme suas aulas confirmadas.
 */
class ProfessorPagamento extends Model
{
    // LEGENDA: Define o nome exato da tabela criada no SQL
    protected $table = 'professor_pagamentos';

    // LEGENDA: Campos permitidos para preenchimento (Mass Assignment)
    protected $fillable = [
        'escala_id',
        'user_id',
        'valor_pago',
        'data_referencia',
        'status_pagamento',
        'liquidacao_id',
    ];

    /**
     * LEGENDA: Casts de Dados (Poka-Yoke)
     * Garante que os valores venham como decimais e a data como objeto Carbon.
     */
    protected $casts = [
        'valor_pago'      => 'decimal:2',
        'data_referencia' => 'date',
    ];

    /**
     * RELACIONAMENTO: Pertence a uma Escala
     * LEGENDA: Liga o pagamento de volta à escala de origem.
     */
    public function escala(): BelongsTo
    {
        return $this->belongsTo(Escala::class, 'escala_id');
    }

    /**
     * RELACIONAMENTO: Pertence a um Professor (User)
     * LEGENDA: Identifica qual professor recebeu o pagamento.
     */
    public function professor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * RELACIONAMENTO: Pertence a uma Liquidação (opcional)
     * LEGENDA: Se preenchido, significa que este pagamento já foi liquidado/pago
     */
    public function liquidacao(): BelongsTo
    {
        return $this->belongsTo(ProfessorLiquidacao::class, 'liquidacao_id');
    }
}
