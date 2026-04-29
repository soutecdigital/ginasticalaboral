@extends('layouts.main')

@section('content')
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Destaques do Mês</h3>
                <p class="text-muted small">Reconhecimento baseado nos elogios da Ouvidoria</p>
            </div>
            <div class="text-end">
                <span class="badge bg-dark px-3 py-2">{{ now()->translatedFormat('F / Y') }}</span>
            </div>
        </div>

        {{-- PIRÂMIDE / PÓDIO DE RECONHECIMENTO --}}
        {{-- PIRÂMIDE / PÓDIO DE RECONHECIMENTO --}}
        <div class="row justify-content-center align-items-end mb-5 text-center" style="min-height: 400px;">

            @foreach ($destaques->take(3) as $index => $item)
                @php
                    // Lógica de Pódio
                    $config = [
                        0 => ['ordem' => 2, 'cor' => '#ffc107', 'label' => 'LÍDER'],
                        1 => ['ordem' => 1, 'cor' => '#c0c0c0', 'label' => '2º LUGAR'],
                        2 => ['ordem' => 3, 'cor' => '#cd7f32', 'label' => '3º LUGAR'],
                    ][$index];
                @endphp

                <div class="col-md-3 px-1 animate__animated animate__fadeInUp" style="order: {{ $config['ordem'] }};">
                    <div class="mb-3">
                        <div class="position-relative d-inline-block">
                            {{-- LEGENDA: Avatar Profissional com Padrão 'default.png' --}}
                            <img src="{{ asset('storage/fotos/' . ($item->professor?->foto ?? 'default.png')) }}"
                                class="rounded-circle border {{ $index == 0 ? 'border-warning' : 'border-light' }} shadow-lg"
                                style="width: {{ $index == 0 ? '140px' : '110px' }}; height: {{ $index == 0 ? '140px' : '110px' }}; object-fit: cover; border-width: 5px !important;"
                                alt="Foto do Professor">

                            {{-- LEGENDA: Badge de ID (Opcional, mas útil para o ranking) --}}
                            @if ($item->professor)
                                <span
                                    class="badge rounded-pill bg-dark position-absolute bottom-0 start-50 translate-middle-x mb-n3"
                                    style="font-size: 0.6rem;">
                                    ID: {{ $item->professor->id }}
                                </span>
                            @endif
                        </div>

                        {{-- LEGENDA: Nome Completo com proteção Nullsafe (?->) --}}
                        <h5 class="fw-bold mt-4 mb-0 text-truncate {{ $item->professor ? '' : 'text-danger italic' }}">
                            {{ $item->professor?->name ?? 'Professor Externo/Removido' }}
                        </h5>
                    </div>

                    {{-- O RESTO DO SEU PÓDIO CONTINUA AQUI... --}}
                    <div class="shadow-sm rounded-top d-flex flex-column justify-content-center align-items-center"
                        style="background-color: {{ $config['cor'] }}; height: {{ $index == 0 ? '320px' : ($index == 1 ? '240px' : '180px') }}; color: {{ $index == 0 ? '#000' : '#fff' }};">
                        {{-- ... conteúdo do pódio ... --}}
                    </div>
                </div>
            @endforeach
        </div>

        <hr class="my-5 opacity-25">

        {{-- LISTA COMPLETA (Caso tenha mais professores) --}}
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h5 class="fw-bold mb-3">Ranking Geral de Elogios</h5>
                <div class="card border-0 shadow-sm">
                    <ul class="list-group list-group-flush">
                        @foreach ($destaques->skip(3) as $index => $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark me-3">{{ $index + 4 }}º</span>
                                    <span class="fw-bold">{{ $item->professor->name }}</span>
                                </div>
                                <span class="badge bg-success rounded-pill px-3">{{ $item->total }} elogios</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
