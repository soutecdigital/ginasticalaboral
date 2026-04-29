@extends('errors::minimal')

@section('title', __('Erro no Servidor'))
@section('code', '500')

@section('message')
    <div style="padding: 20px;">
        <h3 style="color: #333; font-weight: bold;">⚠️ Erro no Banco de Dados</h3>
        <p style="color: #666;">
            O sistema encontrou um problema técnico interno.
            <br><br>
            <strong>Sugestões para o usuário:</strong>
        <ul style="text-align: left; display: inline-block; color: #666;">
            <li>Verifique se você selecionou uma <strong>unidade</strong> no início.</li>
            <li>Tente atualizar a página (F5).</li>
            <li>Se o problema persistir, informe ao suporte da <strong>SouTecDigital</strong>.</li>
        </ul>
        </p>

        @if (isset($message))
            <div
                style="margin-top: 30px; background: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545; border-radius: 4px;">
                <p style="margin: 0; color: #666; font-size: 14px;">
                    <strong style="color: #dc3545;">Detalhes técnicos:</strong>
                    <br>
                    <code
                        style="display: block; margin-top: 10px; background: #fff; padding: 10px; border-radius: 3px; overflow-x: auto; color: #d32f2f; font-family: monospace; font-size: 12px;">
                        {{ $message }}
                    </code>
                </p>
                @if (isset($code))
                    <p style="margin: 10px 0 0 0; color: #999; font-size: 12px;">
                        Código: <strong>{{ $code }}</strong>
                    </p>
                @endif
            </div>
        @endif

        <a href="{{ url('/') }}"
            style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px;">
            Voltar para o Início
        </a>
    </div>
@endsection
