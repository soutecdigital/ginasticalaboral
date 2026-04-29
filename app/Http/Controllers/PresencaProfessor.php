<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresencaProfessor extends Model
{
    // LEGENDA: Define explicitamente a tabela no banco (Evita erro de pluralização do Laravel)
    protected $table = 'presencas_professores';

    // LEGENDA: Campos permitidos para inserção em massa (Segurança contra Mass Assignment)
    protected $fillable = ['user_id', 'empresa_id', 'dupla_id', 'data'];

    /**
     * RELACIONAMENTOS (O "Coração" do seu Relatório e Agenda)
     */

    // POKA-YOKE: Se você chamar '->professor' na View, ele traz os dados do User (ID do professor logado)
    public function professor() 
    { 
        return $this->belongsTo(User::class, 'user_id'); 
    }

    // POKA-YOKE: Identifica o parceiro de aula se houver um dupla_id preenchido
    public function dupla() 
    { 
        return $this->belongsTo(User::class, 'dupla_id'); 
    }

    // POKA-YOKE: Liga o check-in à unidade da Google ou outra empresa do cadastro
    public function empresa() 
    { 
        return $this->belongsTo(Empresa::class, 'empresa_id'); 
    }
}