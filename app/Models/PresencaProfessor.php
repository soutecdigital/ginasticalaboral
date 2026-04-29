<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PresencaProfessor extends Model
{
    use HasFactory;

    // 1. Define o nome exato da tabela no MySQL
    protected $table = 'presencas_professores';

    // 2. Define quais campos podem ser preenchidos (Segurança)
    protected $fillable = [
        'user_id',
        'empresa_id',
        'dupla_id',
        'data'
    ];

    // 3. Relacionamento: Quem é o professor que deu check-in
    public function professor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 4. Relacionamento: Quem é a dupla (se houver)
    public function dupla()
    {
        return $this->belongsTo(User::class, 'dupla_id');
    }

    // 5. Relacionamento: Em qual empresa foi a aula
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}