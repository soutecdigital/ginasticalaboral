<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    // use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'nome_fantasia',
        'razao_social',
        'cnpj',
        'cidade',
        'estado',
        'numero',
        'celular',
        'contato',
        'observacao',
        'plano',
        'ativo',
        'valor_contrato',
        'dia_vencimento',
        'seg',
        'ter',
        'qua',
        'qui',
        'sex',
        'sab',
        'dom', // <-- Adicionado (Domingo)
        'logo_path', // <-- Adicionado
        'raio_gps_metros', // <-- Adicionado
        'logradouro',
        'bairro',
        'lat',
        'lng',
        'user_id', // <-- Adicionado (Dono/Criador da empresa)
    ];

    /**
     * LEGENDA: Conversão de Dados (Casts)
     * Garante que o Laravel trate os dados corretamente (ex: booleano para os dias e decimal para dinheiro)
     * ao sair do banco de dados, evitando erros de cálculo ou lógica.
     */
    protected $casts = [
        'ativo' => 'boolean',
        'valor_contrato' => 'decimal:2',
        'dia_vencimento' => 'integer',
        'seg' => 'boolean',
        'ter' => 'boolean',
        'qua' => 'boolean',
        'qui' => 'boolean',
        'sex' => 'boolean',
        'sab' => 'boolean',
        'dom' => 'boolean', // <-- Adicionado
        'raio_gps_metros' => 'decimal:2', // <-- Adicionado
        'lat' => 'float', // <-- Adicionado para facilitar uso com mapas/APIs
        'lng' => 'float', // <-- Adicionado para facilitar uso com mapas/APIs
        'user_id' => 'integer', // <-- Adicionado
    ];

    /**
     * LEGENDA: Relacionamento com o Usuário Criador (Opcional, mas recomendado)
     * Se o 'user_id' na tabela empresas indicar qual usuário gerencia ou criou essa empresa.
     */
    public function criador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * LEGENDA: Vínculo de Alunos/Usuários (Muitos-para-Muitos)
     * Utiliza a tabela pivô 'empresa_user' para ligar os alunos à unidade.
     * Permite usar: $empresa->users()->where('perfil', 'aluno')->get()
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'empresa_user');
    }

    /**
     * LEGENDA: Presenças dos Alunos (Um-para-Muitos)
     * Liga a empresa à tabela 'presencas' onde os alunos confirmam a aula.
     * CRÍTICO para o contador de alunos na sua nova Agenda.
     */
    public function presencas(): HasMany
    {
        return $this->hasMany(Presenca::class, 'empresa_id');
    }

    /**
     * LEGENDA: Check-in do Professor (Um-para-Muitos)
     * Registra quando o professor abriu a aula na unidade.
     * Alimenta o status "Aula Realizada" e a "Estrelinha" na Agenda.
     */
    public function presencasProfessores(): HasMany
    {
        return $this->hasMany(PresencaProfessor::class, 'empresa_id');
    }

    /**
     * LEGENDA: Gestão Financeira (Faturamentos)
     * Rastreia todos os boletos/faturas gerados para esta unidade específica.
     */
    public function faturamentos(): HasMany
    {
        return $this->hasMany(Faturamento::class);
    }

    /**
     * LEGENDA: Histórico de Contratos
     * Guarda as alterações contratuais para auditoria futura.
     */
    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoContrato::class, 'empresa_id')->orderBy('created_at', 'desc');
    }

    /**
     * LEGENDA: Acessor de Máscara (CNPJ)
     * Formata automaticamente o CNPJ na View: $empresa->cnpj_formatado.
     * Poka-Yoke: Limpa pontos e traços antes de aplicar a máscara.
     */
    public function getCnpjFormatadoAttribute()
    {
        $cnpj = preg_replace("/\D/", '', $this->cnpj);
        if (strlen($cnpj) === 14) {
            return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cnpj);
        }
        return $this->cnpj;
    }
}
