@extends('errors::minimal')

@section('title', __('Erro no Servidor'))
@section('code', '500')

@section('message')
    <div style="padding: 20px;">
        <h3 style="color: #333; font-weight: bold;">Opa! Algo não saiu como esperado.</h3>
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
        <a href="{{ url('/') }}"
            style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px;">
            Voltar para o Início
        </a>
    </div>
@endsection
