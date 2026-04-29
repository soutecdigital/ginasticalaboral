<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: ProfessorLiquidacao
 * Objetivo: Registrar pagamentos já realizados aos professores (com NF e data)
 * LEGENDA: Diferente de ProfessorPagamento, aqui rastreamos a liquidação efetiva
 */
class ProfessorLiquidacao extends Model
{
    protected $table = 'professor_liquidacoes';

    protected $fillable = [
        'professor_id',
        'empresa_id',
        'numero_nf',
        'mes_referencia',
        'valor_total_pago',
        'data_pagamento',
        'forma_pagamento',
        'user_baixa_id',
        'observacao',
    ];

    protected $casts = [
        'valor_total_pago' => 'decimal:2',
        'mes_referencia'   => 'date',
        'data_pagamento'   => 'date',
    ];

    /**
     * RELACIONAMENTO: Pertence a um Professor (User)
     */
// Deve ter 'pagamentos' no plural para o with(['pagamentos.escala'])
public function pagamentos() {
    
    return $this->hasMany(ProfessorPagamento::class, 'liquidacao_id');
}

// Relacionamento com quem deu a baixa
public function usuarioBaixa() {
    return $this->belongsTo(User::class, 'user_baixa_id');
}

public function professor() {
    return $this->belongsTo(User::class, 'professor_id');
}

public function empresa() {
    return $this->belongsTo(Empresa::class, 'empresa_id');
}
}

