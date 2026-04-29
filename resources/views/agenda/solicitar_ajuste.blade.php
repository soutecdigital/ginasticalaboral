@extends('layouts.main')

@section('content')
    <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 80vh;">
        <div class="card border-0 shadow-lg text-center p-4" style="max-width: 400px; border-radius: 20px;">
            <div class="card-body">
                {{-- Ícone Animado de Sucesso --}}
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>

                <h4 class="fw-bold text-dark mb-2">Solicitação Enviada!</h4>
                <p class="text-muted small mb-4">
                    Sua solicitação de ajuste para a aula na unidade <br>
                    <strong>{{ $escala->empresa->nome_fantasia }}</strong> <br>
                    referente ao dia <strong>{{ \Carbon\Carbon::parse($escala->data)->format('d/m/Y') }}</strong>
                    foi enviada para validação do Sócio.
                </p>

                <div class="alert alert-warning border-0 small py-2 mb-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Aguarde a confirmação para que o valor seja creditado em sua conta.
                </div>

                {{-- Botão de Retorno Rápido --}}
                <a href="{{ route('agenda.index') }}" class="btn btn-primary w-100 fw-bold py-2 shadow">
                    <i class="bi bi-arrow-left me-2"></i> VOLTAR PARA MINHA AGENDA
                </a>
            </div>
        </div>
    </div>

    {{-- Poka-Yoke: Redirecionamento automático após 5 segundos --}}
    <script>
        setTimeout(function() {
            window.location.href = "{{ route('agenda.index') }}";
        }, 5000);
    </script>
@endsection
