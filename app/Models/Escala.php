<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escala extends Model
{
   // app/Models/Escala.php

protected $fillable = [
    'user_id',
    'empresa_id',
    'data',
    'turno',
    'tipo_aula',
    'valor_venda_avulso',
    'observacao',    
    'status_cancelamento',
    'data_cancelamento',
    'user_cancelamento_id',
    'observacao_cancelamento',
    'checkin',
    'status_presenca',
    'lat_prof',
    'lng_prof',
    'geo_valid'
];
    /**
     * LEGENDA: O Professor que o Sócio planejou para a aula.
     */
    public function professor() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * LEGENDA: A Unidade/Fábrica onde a aula deve acontecer.
     */
    public function empresa() {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

public function configuracaoAtual()
{
    return $this->hasOne(ProfessorConfiguracao::class, 'user_id')
                ->latest('data_inicio_vigencia');
}

/**
 * Relacionamento: Localizações registradas do professor durante a confirmação
 */
public function localizacoes()
{
    return $this->hasMany(LocalizacaoProfEmp::class, 'escala_id');
}

/**
 * Relacionamento: O usuário (Sócio/Admin) que realizou o cancelamento.
 */
public function usuarioCancelamento()
{
    // Conecta o user_cancelamento_id da tabela escalas ao id da tabela users
    return $this->belongsTo(User::class, 'user_cancelamento_id');
}

}