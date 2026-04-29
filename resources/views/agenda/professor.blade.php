@extends('layouts.main')

@section('content')
    @php
        // 🛡️ Tradução simples para os dias
        $diasPt = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
        ];

        $escalasSimples = $escalas->flatten();
        $totalSemana = $escalasSimples->sum(function ($escala) {
            return $escala->valor_venda_avulso > 0
                ? $escala->valor_venda_avulso
                : $escala->professor->configuracaoAtual->valor_aula ?? 0;
        });

        $hojeRealObj = \Carbon\Carbon::now()->startOfDay();
        $hojeString = $hojeRealObj->format('Y-m-d');
    @endphp

    <style>
        body {
            background-color: #f4f6f9;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .total-box {
            background: linear-gradient(135deg, #051e4d 0%, #051e4d  100%);
            color: white;
            border-radius: 12px;
            padding: 15px;
        }

        .card-dia {
            border: none;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(39, 3, 122, 0.05);
        }

        .aula-item {
            background: #fff;
            border-left: 6px solid #5e72e4;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .btn-acao {
            min-height: 48px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>

    <div class="container-fluid px-3 mt-3">
        {{-- Resumo de Ganhos --}}
        <div class="total-box mb-3 shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <div><small class="text-uppercase opacity-75 d-block"
                        style="font-size: 0.6rem;">Professor:</small><strong>{{ auth()->user()->name }}</strong></div>
                <div class="text-end"><small class="text-uppercase opacity-75 d-block" style="font-size: 0.6rem;">Total
                        Semana</small><strong>R$ {{ number_format($totalSemana, 2, ',', '.') }}</strong></div>
            </div>
        </div>

        {{-- Navegação Semanal --}}
        <div class="d-flex justify-content-between align-items-center bg-white p-2 mb-3 rounded-pill shadow-sm border">
            <a href="{{ route('agenda.index', ['data' => $inicioSemana->copy()->subWeek()->format('Y-m-d')]) }}"
                class="btn btn-sm btn-primary rounded-circle"><i class="bi bi-chevron-left"></i></a>
            <span class="fw-bold small text-primary">
                {{ $inicioSemana->format('d/m') }} — {{ $inicioSemana->copy()->addDays(6)->format('d/m') }}
            </span>
            <a href="{{ route('agenda.index', ['data' => $inicioSemana->copy()->addWeek()->format('Y-m-d')]) }}"
                class="btn btn-sm btn-primary rounded-circle"><i class="bi bi-chevron-right"></i></a>
        </div>

        {{-- LOOP CORRIGIDO: 7 dias a partir do início da semana --}}
        @for ($i = 0; $i < 7; $i++)
            @php
                $dataLoopObj = $inicioSemana->copy()->addDays($i)->startOfDay();
                $dataLoop = $dataLoopObj->format('Y-m-d');
                $isHoje = $dataLoop === $hojeString;
                $diaNome = $diasPt[$dataLoopObj->dayOfWeek];
                $escalasDoDia = $escalasSimples->where('data', $dataLoop);
            @endphp

            <div class="card card-dia {{ $isHoje ? 'border-primary border-2' : '' }}" data-date="{{ $dataLoop }}">
                <div class="card-header {{ $isHoje ? 'bg-primary' : 'bg-dark' }} text-white py-2">
                    <div class="d-flex justify-content-between">
                        <strong>{{ strtoupper($diaNome) }}</strong>
                        <small>{{ $dataLoopObj->format('d/m') }}</small>
                    </div>
                </div>

                <div class="card-body p-2">
                    @forelse ($escalasDoDia as $escala)
                        @php
                            $confirmada = $escala->status_presenca === 'confirmada' || $escala->checkin;
                            $atrasada = !$confirmada && $dataLoopObj->lt($hojeRealObj);
                        @endphp

                        <div class="aula-item mb-2">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong class="d-block">{{ $escala->empresa->nome_fantasia }}</strong>
                                    <span
                                        class="badge bg-light text-primary border">{{ strtoupper($escala->turno) }}</span>
                                </div>
                                <span class="text-success fw-bold">
                                    R$ {{ number_format($escala->valor_venda_avulso ?? 0, 2, ',', '.') }}
                                </span>
                            </div>

                            <div class="mt-3 pt-2 border-top">
                                @if ($confirmada)
                                    <button class="btn btn-outline-success w-100 btn-acao" disabled>
                                        <i class="bi bi-check-circle-fill me-2"></i> CONCLUÍDO
                                    </button>
                                @elseif ($atrasada)
                                    @php
                                        $jaSolicitou =
                                            $escala->solicitou_ajuste ||
                                            str_contains($escala->observacao, 'Ajuste solicitado');
                                    @endphp

                                    @if ($jaSolicitou)
                                        <div class="alert alert-warning py-2 mb-0 small text-center fw-bold">
                                            <i class="bi bi-hourglass-split me-1"></i> AJUSTE EM ANÁLISE...
                                        </div>
                                    @else
                                        <div class="text-danger small fw-bold mb-1"><i
                                                class="bi bi-exclamation-triangle"></i> Esquecimento Detectado</div>
                                        <a href="{{ route('agenda.solicitar_ajuste', $escala->id) }}"
                                            onclick="return bloquearBotao(this)"
                                            class="btn btn-warning w-100 btn-acao shadow-sm">
                                            SOLICITAR AJUSTE RETROATIVO
                                        </a>
                                    @endif
                                @elseif ($isHoje)
                                    <button type="button" class="btn btn-success w-100 btn-acao shadow"
                                        onclick="iniciarGeolocalizacao(event, '{{ $escala->id }}', {{ $escala->empresa->lat ?? 0 }}, {{ $escala->empresa->lng ?? 0 }})">
                                        <i class="bi bi-geo-alt-fill me-2"></i> CONFIRMAR AGORA
                                    </button>
                                @else
                                    <button class="btn btn-light w-100 btn-acao text-muted" disabled>
                                        <i class="bi bi-clock me-2"></i> AGUARDAR DATA
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-2 text-muted opacity-50 small">Sem escalas</div>
                    @endforelse
                </div>
            </div>
        @endfor
    </div>

    <form id="form-checkin-global" method="POST" style="display:none;">@csrf
        <input type="hidden" name="lat_prof" id="lat_prof">
        <input type="hidden" name="lng_prof" id="lng_prof">
        <input type="hidden" name="motivo_gps_fraco" id="motivo_gps_fraco">
    </form>

    <script>
        let processando = false;

        /**
         * Inicia geolocalização e captura coordenadas do professor
         * Compara com coordenadas da empresa para alertar se está fora
         */
        function iniciarGeolocalizacao(event, id, empresaLat, empresaLng) {
            if (processando) return;
            const btn = event.currentTarget;
            processando = true;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>BUSCANDO GPS...';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const profLat = pos.coords.latitude;
                    const profLng = pos.coords.longitude;

                    // Calcula distância usando Haversine (em km)
                    const R = 6371; // Raio da Terra em km
                    const dLat = (empresaLat - profLat) * Math.PI / 180;
                    const dLng = (empresaLng - profLng) * Math.PI / 180;
                    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(profLat * Math.PI / 180) * Math.cos(empresaLat * Math.PI / 180) *
                        Math.sin(dLng / 2) * Math.sin(dLng / 2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    const distancia_km = R * c;
                    const distancia_m = distancia_km * 1000;

                    // 🛡️ Verifica se está dentro do raio (500m padrão)
                    const raio_tolerancia = 500; // metros
                    const fora_do_raio = distancia_m > raio_tolerancia;

                    if (fora_do_raio) {
                        if (!confirm(
                                `⚠️ AVISO: Você está ${Math.round(distancia_m)}m fora do mapa da empresa (tolerância: ${raio_tolerancia}m).\n\nDeseja continuar?`
                            )) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-geo-alt-fill me-2"></i> CONFIRMAR AGORA';
                            processando = false;
                            return;
                        }
                    }

                    enviar(id, profLat, profLng, null);
                },
                (error) => {
                    // GPS fraco ou não disponível
                    let motivo = 'Sinal GPS fraco ou não disponível';

                    if (error.code === error.PERMISSION_DENIED) {
                        motivo = 'Permissão de localização negada';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        motivo = 'Localização indisponível (sinal fraco)';
                    } else if (error.code === error.TIMEOUT) {
                        motivo = 'Timeout ao obter localização (sinal fraco)';
                    }

                    alert("⏱️ Sinal GPS fraco. Confirmando por horário.\n\nMotivo: " + motivo);
                    enviar(id, null, null, motivo);
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function enviar(id, lat, lng, motivo_gps) {
            const f = document.getElementById('form-checkin-global');
            f.action = "{{ url('/agenda/checkin') }}/" + id;
            document.getElementById('lat_prof').value = lat || '';
            document.getElementById('lng_prof').value = lng || '';
            document.getElementById('motivo_gps_fraco').value = motivo_gps || '';
            f.submit();
        }

        function bloquearBotao(el) {
            if (processando) return false;
            processando = true;
            el.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>ENVIANDO...';
            el.classList.add('disabled', 'opacity-50');
            return true;
        }
    </script>
@endsection
