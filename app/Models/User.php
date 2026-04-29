<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model User - Representa Professores, Sócios e Admins.
 * 🛡️ POKA-YOKE: Centraliza permissões e vínculos com empresas.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** @var array Atributos que podem ser preenchidos em massa (Mass Assignment) */
    protected $fillable = [
        'matricula', 'name', 'email', 'password', 'perfil', 
        'user_creator', 'data_creator', 'cpf', 'ativo',
    ];

    /** @var array Atributos ocultos em respostas JSON/Arrays para segurança */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Casting de Atributos: Converte dados do banco para tipos nativos do PHP.
     * @return array
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'ativo' => 'boolean', // Converte 0/1 do MySQL para true/false
            'data_creator' => 'datetime',
        ];
    }

    /**
     * RELACIONAMENTO: Vínculo com as Unidades/Fábricas.
     * Define em quais empresas este usuário pode atuar.
     */
    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_user', 'user_id', 'empresa_id');
    }

    /**
     * RELACIONAMENTO (Poka-Yoke Financeiro): 
     * Pega a configuração de valor de aula mais recente baseada na data de vigência.
     */
    public function configuracaoAtual(): HasOne
    {
        return $this->hasOne(ProfessorConfiguracao::class, 'user_id')
                    ->latestOfMany('data_inicio_vigencia');
    }

    /**
     * HELPERS DE PERFIL: Atalhos para checar permissões sem repetir lógica de string.
     */
    public function isAdmin(): bool { return $this->perfil === 'admin'; }
    public function isSocio(): bool { return $this->perfil === 'socio'; }
    public function isProfessor(): bool { return $this->perfil === 'professor'; }
}