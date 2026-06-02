<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empresa;
use App\Models\ProfessorConfiguracao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Controller: Gestão de Usuários e Performance Operacional
 * Objetivo: Administrar perfis de acesso e regras financeiras (custo/aula).
 */
class UserController extends Controller
{
    /**
     * MÉTODO: index
     * LEGENDA: Lista usuários e suas empresas. Usa Eager Loading ('with') 
     * para performance, evitando múltiplas consultas ao banco.
     */
    public function index()
    {
        $users = User::with('empresas')->orderBy('name')->get();
        return view('usuarios.index', compact('users'));
    }

    /**
     * MÉTODO: create
     * LEGENDA: Prepara a tela de novo usuário. Filtra apenas empresas ativas
     * para evitar erros de logística no vínculo inicial.
     */
    public function create()
    {
        $empresas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();
        return view('usuarios.create', compact('empresas'));
    }

    /**
     * MÉTODO: store
     * LEGENDA: Grava o novo usuário com validações rigorosas (Poka-Yoke)
     * - CPF sanitizado e único
     * - Matrícula obrigatória e única
     * - Empresas obrigatórias (pelo menos uma)
     * - Transação garante atomicidade
     */
    public function store(Request $request)
    {
        try {
            // [SANITIZAÇÃO] Limpa o CPF para validar e salvar apenas os números
            $cpfLimpo = preg_replace('/\D/', '', $request->cpf ?? '');
            
            // [POKA-YOKE] Preparamos os dados antes da validação
            $request->merge([
                'name'      => trim($request->name ?? ''),
                'email'     => strtolower(trim($request->email ?? '')),
                'matricula' => trim($request->matricula ?? '') ?: null,
                'cpf'       => $cpfLimpo, // Importante: Valida o CPF já limpo
            ]);

            // [VALIDAÇÃO] Rigorosa com mensagens em Português
            $request->validate([
                'name'      => 'required|string|min:3|max:255',
                'email'     => 'required|email|unique:users,email',
                'password'  => 'required|min:6|confirmed',
                'perfil'    => 'required|in:admin,professor,aluno,socio',
                'matricula' => 'nullable|string|max:255',
                'cpf'       => 'required|digits:11|unique:users,cpf', // CPF deve ter 11 dígitos e ser único
                'empresas'  => 'required|array|min:1',
            ], [
                // Traduções customizadas para evitar o erro "validation.min.string"
                'name.required'      => 'O campo NOME é obrigatório.',
                'name.min'           => 'O nome deve ter pelo menos :min caracteres.',
                'email.required'     => 'O E-MAIL é obrigatório.',
                'email.unique'       => 'Este e-mail já está cadastrado no sistema.',
                'password.required'  => 'A SENHA é obrigatória.',
                'password.min'       => 'A senha deve ter no mínimo :min caracteres.',
                'password.confirmed' => 'A confirmação de senha não confere.',
                'cpf.required'       => 'O CPF é obrigatório.',
                'cpf.digits'         => 'O CPF deve conter exatamente 11 números.',
                'cpf.unique'         => 'Este CPF já está cadastrado.',
                'empresas.required'  => 'Vincule o usuário a pelo menos uma empresa.',
                'empresas.min'       => 'É obrigatório selecionar ao menos uma empresa.',
            ]);

            // [ATOMICIDADE] Garante que o User e o Vínculo ocorram ou nada seja salvo
            return DB::transaction(function () use ($request, $cpfLimpo) {
                
                $user = User::create([
                    'name'         => ucwords(strtolower($request->name)), 
                    'email'        => $request->email,
                    'password'     => Hash::make($request->password),
                    'perfil'       => $request->perfil,
                    'matricula'    => $request->matricula ? mb_strtoupper($request->matricula) : null,
                    'cpf'          => $cpfLimpo,
                    'user_creator' => Auth::id(),
                    'ativo'        => true,
                ]);

                // [VÍNCULO] Anexa as empresas (Relacionamento Many-to-Many)
                if ($request->has('empresas')) {
                    $user->empresas()->attach($request->empresas);
                }

                return redirect()->route('usuarios.index')
                    ->with('success', 'Usuário ' . $user->name . ' cadastrado com sucesso!');
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se for erro de validação, volta com as mensagens específicas do Laravel
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
                
        } catch (Exception $e) {
            // Erros inesperados (Banco de dados, PHP, etc)
            return redirect()->back()
                ->withInput()
                ->with('error', 'Falha operacional: ' . $e->getMessage());
        }
    }

    /**
     * MÉTODO: edit
     * LEGENDA: Carrega os dados para edição. O 'pluck' identifica quais empresas
     * o usuário já atende para marcar o Select2 automaticamente.
     */
    public function edit(User $usuario)
    {
        $empresas = Empresa::where('ativo', true)->orderBy('nome_fantasia')->get();
        $empresasVinculadas = $usuario->empresas->pluck('id')->toArray();

        return view('usuarios.edit', compact('usuario', 'empresas', 'empresasVinculadas'));
    }

    /**
     * MÉTODO: update
     * LEGENDA: Atualiza o perfil com validações. O sync() garante que o usuário
     * tenha apenas as empresas selecionadas, removendo as antigas automaticamente.
     */
    public function update(Request $request, User $usuario)
    {
        try {
            // [SANITIZAÇÃO] Remove caracteres especiais do CPF
            $cpfLimpo = preg_replace('/\D/', '', $request->cpf ?? '');
            
            // [POKA-YOKE] Atualiza o request com os dados tratados ANTES da validação rodar
            $request->merge([
                'name'      => trim($request->name ?? ''),
                'email'     => strtolower(trim($request->email ?? '')),
                'matricula' => trim($request->matricula ?? '') ?: null,
                'cpf'       => $cpfLimpo ?: null, // Passa o CPF limpo para a validação unique do banco funcionar
            ]);
            
            $request->validate([
                'name'      => 'required|string|min:2|max:255',
                'email'     => 'required|email|unique:users,email,' . $usuario->id,
                'perfil'    => 'required|in:admin,professor,aluno,socio',
                'matricula' => 'nullable|string|max:255', 
                'cpf'       => 'required|digits:11|unique:users,cpf,' . $usuario->id,
                'empresas'  => 'required|array|min:1', 
            ], [
                'name.required'      => 'O campo NOME é obrigatório.',
                'name.min'           => 'O nome deve ter pelo menos :min caracteres.',
                'email.required'     => 'O E-MAIL é obrigatório.',
                'email.unique'       => 'Este e-mail já está cadastrado no sistema.',
                'cpf.required'       => 'O CPF é obrigatório.',
                'cpf.digits'         => 'O CPF deve conter exatamente 11 números.',
                'cpf.unique'         => 'Este CPF já está cadastrado em outro usuário.',
                'empresas.required'  => 'Escolha pelo menos uma empresa para vincular.',
                'empresas.min'       => 'É obrigatório vincular a pelo menos uma empresa.',
            ]);

            return DB::transaction(function () use ($request, $usuario, $cpfLimpo) {
                $usuario->name = ucwords(strtolower($request->name)); // Capitaliza
                $usuario->email = $request->email; 
                $usuario->perfil = $request->perfil;
                $usuario->matricula = $request->matricula ? mb_strtoupper($request->matricula) : null; 
                
                // Atualiza CPF sanitizado
                if (!empty($cpfLimpo)) {
                    $usuario->cpf = $cpfLimpo;
                }

                // Só altera a senha se o campo for preenchido
                if ($request->filled('password')) {
                    $usuario->password = Hash::make($request->password);
                }

                $usuario->save();
                
                // [SINCRONIZAÇÃO] Atualiza as empresas vinculadas
                $usuario->empresas()->sync($request->empresas);

                return redirect()->route('usuarios.index')
                    ->with('success', 'Cadastro atualizado com sucesso!');
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Se falhar na validação, volta para o formulário com os erros corretos
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();

        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
        }
    }

    /**
     * MÉTODO: reajuste
     * FUNÇÃO: Gestão de Histórico Financeiro do Professor.
     * LEGENDA: Não sobrescreve o valor antigo; cria um novo registro com data 
     * de vigência. Isso permite auditoria de quanto o professor ganhava em cada mês.
     */
    public function reajuste(Request $request, $id)
    {
        // 1. Validação (Poka-Yoke: garante que os novos campos cheguem)
        $request->validate([
            'valor_aula'           => 'required|numeric',
            'valor_aula_online'    => 'required|numeric',
            'valor_aula_avulso'    => 'required|numeric',
            'data_inicio_vigencia' => 'required|date',
            'observacao'           => 'nullable|string',
        ]);

        // 2. Gravação no Banco
        ProfessorConfiguracao::create([
            'user_id'              => $id,
            'valor_aula'           => $request->valor_aula,
            'valor_aula_online'    => $request->valor_aula_online,
            'valor_aula_avulso'    => $request->valor_aula_avulso,
            'data_inicio_vigencia' => $request->data_inicio_vigencia,
            'observacao'           => $request->observacao,
        ]);

        return redirect()->back()->with('success', 'Configuração de valores atualizada com sucesso!');
    }
}
