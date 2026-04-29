<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaUserPresenca extends Model
{
    protected $table = 'empresa_user_presenca';
    
    // ⚠️ NÃO incluir updated_at
    public $timestamps = false;
    
    // Campos permitidos para inserção
    protected $fillable = [
        'empresa_id',
        'user_id',
        'professor_id',
        'presenca',
        'ativo',
        'created_at'
    ];
    
    // Cast para conversão automática
    protected $casts = [
        'presenca' => 'string', // ✅ ENUM values are strings: '0', '1'
        'ativo' => 'boolean',
        'created_at' => 'datetime'
    ];

    /**
     * Relacionamento com Empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Relacionamento com Aluno (user_id)
     */
    public function aluno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relacionamento com Professor
     */
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }
}
