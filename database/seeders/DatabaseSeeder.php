<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar ou atualizar a empresa padrão
        // Usamos updateOrCreate para garantir que o CNPJ 12.345.678/0001-90 seja único
        $empresa = Empresa::updateOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            [
                'nome_fantasia' => 'Academia Padrão',
                'razao_social' => 'Academia Padrão LTDA',
                'ativo' => true,
            ]
        );

        // 2. Criar ou atualizar o usuário de teste
        $user = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'perfil' => 'admin',
                'ativo' => true,
                'password' => \Illuminate\Support\Facades\Hash::make('password'), // Defina uma senha padrão
            ]
        );

        // 3. Vincular usuário à empresa (Relacionamento Many-to-Many)
        // O método syncWithoutDetaching impede que o Laravel tente criar um vínculo que já existe
        $user->empresas()->syncWithoutDetaching([$empresa->id]);
    }
}