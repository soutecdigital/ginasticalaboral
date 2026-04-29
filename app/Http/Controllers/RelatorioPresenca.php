public function relatorioPresenca(Request $request)
{
    $empresaId = session('empresa_id'); // Poka-Yoke: Pegando da sessão segura
    $inicio = $request->get('inicio', now()->startOfMonth()->toDateString());
    $fim = $request->get('fim', now()->toDateString());

    // Buscamos os usuários através da tabela pivô empresa_user
    $alunos = User::whereHas('empresas', function($q) use ($empresaId) {
        $q->where('empresas.id', $empresaId);
    })
    ->where('perfil', 'aluno')
    ->withCount(['presencas' => function($q) use ($inicio, $fim) {
        $q->whereBetween('data_presenca', [$inicio, $fim])->where('status', 'P');
    }])
    ->withCount(['presencas as faltas_count' => function($q) use ($inicio, $fim) {
        $q->whereBetween('data_presenca', [$inicio, $fim])->where('status', 'F');
    }])
    ->get();

    return view('relatorios.presenca', compact('alunos', 'inicio', 'fim'));
}