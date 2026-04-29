<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoContrato extends Model
{
    protected $table = 'historico_contratos';

    protected $fillable = [
        'empresa_id', 
        'user_id',
        'valor_anterior', 
        'valor_novo', 
        'motivo', 
        'total_alunos_momento'
    ];

    /**
     * Cast de Data: Converte timestamps para Carbon
     */
    protected $casts = [
        'valor_anterior' => 'decimal:2',
        'valor_novo' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento: O histórico pertence a uma empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relacionamento: O histórico foi criado por um usuário (Admin/Sócio)
     * Permite rastrear quem fez a alteração do contrato
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}