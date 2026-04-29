<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ouvidoria extends Model
{
    use HasFactory;

    /**
     * O nome da tabela associada ao modelo.
     * * @var string
     */
    protected $table = 'ouvidorias';

    /**
     * Os atributos que podem ser atribuídos em massa (Mass Assignment).
     * * @var array
     */
    protected $fillable = [
        'user_id',
        'empresa_id',
        'professor_id',
        'assunto',
        'mensagem',
        'status', // pendente, lido, resolvido
        'resposta_coordenacao', // NOVO
        'respondido_em'         // NOVO
    ];

    /**
     * Relacionamento: Retorna o Aluno (User) que enviou o feedback.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relacionamento: Retorna a Unidade (Empresa) associada ao feedback.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

// Dentro da classe Ouvidoria
public function professor()
{
    // LEGENDA: Força o vínculo entre professor_id e o ID da tabela users
    return $this->belongsTo(User::class, 'professor_id', 'id');
}

}
