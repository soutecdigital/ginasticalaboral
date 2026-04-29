@extends('errors::minimal')

@section('title', __('Página Não Encontrada'))
@section('code', '404')

@section('message')
    <div style="padding: 20px; font-family: sans-serif;">
        <div style="font-size: 50px; color: #ffc107; margin-bottom: 20px;">
            <i class="bi bi-search"></i> 🔍
        </div>
        <h3 style="color: #333; font-weight: bold;">Caminho não encontrado!</h3>
        <p style="color: #666; max-width: 400px; margin: 0 auto;">
            A página que você tentou acessar não existe ou foi movida para um novo endereço.
        </p>

        <div style="margin-top: 30px;">
            <a href="{{ url('/') }}"
                style="display: inline-block; padding: 12px 25px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 10px; font-weight: bold; shadow: 0 4px 6px rgba(0,0,0,0.1);">
                Voltar para o Dashboard
            </a>
        </div>

        <p style="margin-top: 20px; color: #999; font-size: 0.8rem;">
            Se você acredita que isso é um erro, contate a <strong>SouTecDigital</strong>.
        </p>
    </div>
@endsection
