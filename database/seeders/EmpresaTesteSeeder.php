<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Empresa; // IMPORTANTE: Endereço do Model Empresa
use App\Models\User;    // IMPORTANTE: Endereço do Model User
use Illuminate\Support\Facades\Hash;

class EmpresaTesteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Criar a Empresa (OEP Soluções)
        // Usamos o updateOrCreate para evitar erro de duplicidade se você rodar 2 vezes
        $empresa = Empresa::updateOrCreate(
            ['cnpj' => '12.345.678/0001-90'], // Procura por este CNPJ
            [
                'nome_fantasia' => 'OEP Soluções',
                'razao_social'  => 'OEP Soluções Tecnológicas LTDA',
                'cidade'        => 'Taubaté',
                'estado'        => 'SP',
                'plano'         => 'premium',
                'ativo'         => true,
            ]
        );

        // 2. Criar o Professor (Você) vinculado à OEP
        User::updateOrCreate(
            ['email' => 'professor@oep.com.br'],
            [
                'name'       => 'Helton Professor',
                'matricula'  => 'PROF001',
                'password'   => Hash::make('123456'), // Senha padrão para teste
                'perfil'     => 'professor',
                'empresa_id' => $empresa->id,
            ]
        );

        // 3. Criar um Aluno de teste vinculado à OEP
        User::updateOrCreate(
            ['email' => 'aluno@oep.com.br'],
            [
                'name'       => 'Aluno Teste Silva',
                'matricula'  => 'ALU001',
                'password'   => Hash::make('123456'),
                'perfil'     => 'aluno',
                'empresa_id' => $empresa->id,
            ]
        );
    }
}