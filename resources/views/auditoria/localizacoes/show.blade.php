@extends('layouts.main')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header p-3 text-white" style="background-color: #1a2a40;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">
                                <i class="bi bi-geo-alt-fill me-2"></i>DETALHES DA LOCALIZAÇÃO
                            </h5>
                            <a href="{{ route('auditoria.localizacoes.index') }}" class="btn btn-light btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>VOLTAR
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            {{-- DADOS DO PROFESSOR --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-person me-2"></i>PROFESSOR
                                </h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    <p class="mb-1"><strong>Nome:</strong> {{ $localizacao->professor->name }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $localizacao->professor->email }}</p>
                                    <p class="mb-0"><strong>CPF:</strong> {{ $localizacao->professor->cpf ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- DADOS DA EMPRESA --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-primary mb-3">
                                    <i class="bi bi-building me-2"></i>EMPRESA
                                </h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    <p class="mb-1"><strong>Nome:</strong> {{ $localizacao->empresa->nome_fantasia }}</p>
                                    <p class="mb-1"><strong>Cidade:</strong>
                                        {{ $localizacao->empresa->cidade }}/{{ $localizacao->empresa->estado }}</p>
                                    <p class="mb-0"><strong>Raio Tolerância:</strong>
                                        {{ number_format($localizacao->empresa_raio_metros, 0) }}m</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            {{-- INFORMAÇÕES DE CONFIRMAÇÃO --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-success mb-3">
                                    <i class="bi bi-check-circle me-2"></i>CONFIRMAÇÃO
                                </h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    <p class="mb-1">
                                        <strong>Tipo:</strong>
                                        @if ($localizacao->tipo_confirmacao === 'gps')
                                            <span class="badge bg-success"><i class="bi bi-geo-alt-fill"></i> GPS</span>
                                        @else
                                            <span class="badge bg-warning"><i class="bi bi-clock"></i> Horário</span>
                                        @endif
                                    </p>
                                    <p class="mb-1"><strong>Data/Hora:</strong>
                                        {{ $localizacao->confirmado_em->format('d/m/Y H:i:s') }}</p>
                                    @if ($localizacao->tipo_confirmacao === 'horario' && $localizacao->motivo_gps_fraco)
                                        <p class="mb-0"><strong>Motivo:</strong> {{ $localizacao->motivo_gps_fraco }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- STATUS DE CONFORMIDADE --}}
                            <div class="col-md-6">
                                <h6 class="fw-bold text-danger mb-3">
                                    <i class="bi bi-shield-check me-2"></i>CONFORMIDADE
                                </h6>
                                <div class="bg-light p-3 rounded mb-3">
                                    <p class="mb-1">
                                        <strong>Status:</strong>
                                        @if ($localizacao->dentro_raio)
                                            <span class="badge bg-success text-white fs-6">✓ DENTRO DO RAIO</span>
                                        @else
                                            <span class="badge bg-danger text-white fs-6">✗ FORA DO RAIO</span>
                                        @endif
                                    </p>
                                    @if ($localizacao->distancia_metros !== null)
                                        <p class="mb-1"><strong>Distância:</strong>
                                            {{ number_format($localizacao->distancia_metros, 0) }}m
                                            (Limite: {{ number_format($localizacao->empresa_raio_metros, 0) }}m)</p>
                                    @endif
                                    <p class="mb-0"><strong>Auditoria:</strong> {{ $localizacao->ip_address }} |
                                        {{ $localizacao->user_agent ? substr($localizacao->user_agent, 0, 50) . '...' : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- COORDENADAS CAPTURADAS --}}
                        <h6 class="fw-bold text-info mb-3 mt-4">
                            <i class="bi bi-map me-2"></i>COORDENADAS GEOGRÁFICAS
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                    <h6 class="fw-bold">📍 Localização da Empresa</h6>
                                    <p class="mb-1"><strong>Latitude:</strong> {{ $localizacao->empresa_lat }}</p>
                                    <p class="mb-0"><strong>Longitude:</strong> {{ $localizacao->empresa_lng }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-success bg-opacity-10 p-3 rounded">
                                    <h6 class="fw-bold">📱 Localização do Professor</h6>
                                    <p class="mb-1"><strong>Latitude:</strong>
                                        {{ $localizacao->prof_lat ?? 'Não capturado' }}</p>
                                    <p class="mb-0"><strong>Longitude:</strong>
                                        {{ $localizacao->prof_lng ?? 'Não capturado' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- MAPA (Se tiver coordenadas) --}}
                        @if ($localizacao->prof_lat && $localizacao->prof_lng && $localizacao->empresa_lat && $localizacao->empresa_lng)
                            <h6 class="fw-bold text-info mb-3 mt-4">
                                <i class="bi bi-geo me-2"></i>VISUALIZAÇÃO NO MAPA
                            </h6>
                            <div id="map"
                                style="height: 400px; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            </div>

                            <link rel="stylesheet"
                                href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

                            <script>
                                const empresaLat = {{ $localizacao->empresa_lat }};
                                const empresaLng = {{ $localizacao->empresa_lng }};
                                const profLat = {{ $localizacao->prof_lat }};
                                const profLng = {{ $localizacao->prof_lng }};
                                const raio = {{ $localizacao->empresa_raio_metros }};

                                const map = L.map('map').setView([empresaLat, empresaLng], 16);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

                                // Marcador da Empresa
                                L.marker([empresaLat, empresaLng]).addTo(map)
                                    .bindPopup(`<strong>Empresa</strong><br>${raio}m de raio`);

                                // Círculo de tolerância
                                L.circle([empresaLat, empresaLng], {
                                    color: 'blue',
                                    fillColor: '#0066ff',
                                    fillOpacity: 0.1,
                                    radius: raio
                                }).addTo(map);

                                // Marcador do Professor
                                const profMarker = L.marker([profLat, profLng], {
                                        icon: L.icon({
                                            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                            iconSize: [25, 41],
                                            iconAnchor: [12, 41],
                                            popupAnchor: [1, -34],
                                            shadowSize: [41, 41]
                                        })
                                    }).addTo(map)
                                    .bindPopup(`<strong>Professor</strong><br>{{ $localizacao->professor->name }}`);

                                // Linha de conexão
                                L.polyline([
                                    [empresaLat, empresaLng],
                                    [profLat, profLng]
                                ], {
                                    color: 'red',
                                    weight: 2,
                                    opacity: 0.7,
                                    dashArray: '5, 5'
                                }).addTo(map);
                            </script>
                        @endif

                        {{-- INFORMAÇÕES TÉCNICAS --}}
                        <h6 class="fw-bold text-secondary mb-3 mt-4">
                            <i class="bi bi-terminal me-2"></i>INFORMAÇÕES TÉCNICAS
                        </h6>
                        <div class="bg-dark text-light p-3 rounded small font-monospace">
                            <p class="mb-1"><strong>ID Localização:</strong> {{ $localizacao->id }}</p>
                            <p class="mb-1"><strong>ID Escala:</strong> {{ $localizacao->escala_id }}</p>
                            <p class="mb-1"><strong>ID Professor:</strong> {{ $localizacao->professor_id }}</p>
                            <p class="mb-1"><strong>IP Address:</strong> {{ $localizacao->ip_address }}</p>
                            <p class="mb-1"><strong>User Agent:</strong> <small>{{ $localizacao->user_agent }}</small>
                            </p>
                            <p class="mb-1"><strong>Criado em:</strong>
                                {{ $localizacao->created_at->format('d/m/Y H:i:s') }}</p>
                            <p class="mb-0"><strong>Atualizado em:</strong>
                                {{ $localizacao->updated_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
