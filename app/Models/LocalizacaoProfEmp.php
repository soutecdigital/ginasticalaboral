<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalizacaoProfEmp extends Model
{
    protected $table = 'localizacao_prof_emp';

    protected $fillable = [
        'escala_id',
        'professor_id',
        'empresa_id',
        'empresa_lat',
        'empresa_lng',
        'empresa_raio_metros',
        'prof_lat',
        'prof_lng',
        'distancia_metros',
        'dentro_raio',
        'tipo_confirmacao',
        'motivo_gps_fraco',
        'confirmado_em',
        'user_agent',
        'ip_address',
        'observacao',
    ];

    protected $casts = [
        'empresa_lat' => 'decimal:8',
        'empresa_lng' => 'decimal:8',
        'empresa_raio_metros' => 'decimal:2',
        'prof_lat' => 'decimal:8',
        'prof_lng' => 'decimal:8',
        'distancia_metros' => 'decimal:2',
        'dentro_raio' => 'boolean',
        'confirmado_em' => 'datetime',
    ];

    /**
     * Relacionamento: A escala/aula associada
     */
    public function escala()
    {
        return $this->belongsTo(Escala::class);
    }

    /**
     * Relacionamento: O professor
     */
    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    /**
     * Relacionamento: A empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Helper: Calcula a distância entre dois pontos usando Haversine
     * @param float $lat1 Latitude 1
     * @param float $lon1 Longitude 1
     * @param float $lat2 Latitude 2
     * @param float $lon2 Longitude 2
     * @return float Distância em metros
     */
    public static function calcularDistanciaHaversine($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371000; // Raio da Terra em metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
