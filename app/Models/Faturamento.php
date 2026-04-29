<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faturamento extends Model
{
    protected $fillable = [
        'empresa_id', 
        'valor_mensalidade', 
        'valor_avulso', 
        'mes_referencia', 
        'data_pagamento', 
        'status',
        'user_baixa_id',          // NOVO: Para saber quem deu a baixa
        'observacao_financeira'   // NOVO: Para gravar o log do "choro" do cliente
    ];

    protected $casts = [
        'mes_referencia' => 'date',
        'data_pagamento' => 'date',
        'valor_mensalidade' => 'float',
        'valor_avulso' => 'float',
    ];

    // Relacionamento com a Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    // Relacionamento com o Usuário que deu a baixa (Auditoria)
    public function usuarioBaixa()
    {
        return $this->belongsTo(User::class, 'user_baixa_id');
    }
}