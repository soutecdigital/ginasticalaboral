<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EmpresaScope; // <-- VERIFIQUE ESTA LINHA
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function Symfony\Component\String\u;

use Illuminate\Database\Eloquent\Builder;
class Presenca extends Model
{
    // O nome da tabela no plural (Padrão Laravel)
    protected $table = 'presencas';

// app/Models/Presenca.php
protected $fillable = ['professor_id', 'empresa_id', 'data_presenca', 'user_id', 'hora_presenca', 'observacoes'];

 // Verifique se existe algo assim nos seus Models!
protected static function booted() {
    static::addGlobalScope('empresa', function (Builder $builder) {
        $builder->where('empresa_id', session('empresa_ativa'));
    });
}

    public function aluno()
    {
        return $this->belongsTo(Aluno::class);
    }

    /**
     * RELACIONAMENTO: A presença pertence a uma empresa
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * RELACIONAMENTO: A presença pertence a um professor (User)
     */
    public function professor(): BelongsTo
    {
        // Aqui dizemos: O campo 'user_id' desta tabela aponta para o 'id' da tabela 'users'
        return $this->belongsTo(User::class, 'user_id');
    }


    


}