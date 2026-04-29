<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Empresa;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar empresa padrão
        $empresa = Empresa::factory()->create([
            'nome_fantasia' => 'Academia Padrão',
            'razao_social' => 'Academia Padrão LTDA',
            'cnpj' => '12.345.678/0001-90',
        ]);

        // Criar usuário de teste
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'perfil' => 'admin',
            'ativo' => true,
        ]);

        // Vincular usuário à empresa
        $user->empresas()->attach($empresa->id);
    }
}
