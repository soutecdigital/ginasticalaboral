<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aluno extends Model
{
    // Poka-Yoke: Campos que o Laravel tem permissão para gravar
    protected $fillable = [
        'nome',
        'email',
        'matricula',
        'empresa_id', // FK obrigatória para o multi-tenancy funcionar
        'status'      // Ex: ativo/inativo
    ];

    /**
     * O "Filtro Invisível"
     * Garante que Aluno::all() traga apenas alunos da unidade selecionada na sessão.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }

    /**
     * Relacionamento: O aluno pertence a uma Empresa específica
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Relacionamento: Um aluno tem muitos registros de presença
     */
    public function presencas(): HasMany
    {
        return $this->hasMany(Presenca::class);
    }
}