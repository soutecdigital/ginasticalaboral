<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome_fantasia' => fake()->company(),
            'razao_social' => fake()->company(),
            'cnpj' => fake()->unique()->numerify('##.###.###/####-##'),
            'celular' => fake()->phoneNumber(),
            'contato' => fake()->name(),
            'observacao' => fake()->sentence(),
            'cidade' => 'Taubaté',
            'estado' => 'SP',
            'numero' => fake()->numerify('###'),
            'seg' => true,
            'ter' => true,
            'qua' => true,
            'qui' => true,
            'sex' => true,
            'sab' => false,
            'dom' => false,
            'ativo' => true,
            'lat' => -23.0231,
            'lng' => -45.5502,
        ];
    }
}
