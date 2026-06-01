<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criar ou atualizar a empresa padrão
        $empresa = Empresa::updateOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            [
                'nome_fantasia' => 'Academia Padrão',
                'razao_social'  => 'Academia Padrão LTDA',
                'ativo'         => true,
            ]
        );

        // 2. Criar ou atualizar o SEU usuário administrador real
        $user = User::updateOrCreate(
            ['email' => 'admin@ginasticalaboral.com'], // O e-mail que você quer usar
            [
                'name'          => 'Helton Rodrigues',
                'perfil'        => 'admin',
                'matricula'     => 'ADM001',
                'cpf'           => '12345678900',
                'ativo'         => true,
                'user_creator'  => 1,                 // Evita o erro de sintaxe inteira do Postgres
                'data_creator'  => now()->toDateString(),
                'password'      => Hash::make('admin123'), // A senha correta criptografada pelo PHP
            ]
        );

        // 3. Vincular usuário à empresa (Necessário para a sua estrutura)
        if (method_exists($user, 'empresas')) {
            $user->empresas()->syncWithoutDetaching([$empresa->id]);
        }
    }
}
