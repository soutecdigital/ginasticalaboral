@extends('layouts.main')

@section('content')
    <style>
        /* Estética Moderna & Industrial */
        .agenda-container {
            background: #f8f9fa;
            border-radius: 12px;
        }

        .table-agenda {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table-agenda thead th {
            background-color: #1a2a40;
            color: #ffc107;
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            padding: 12px;
            text-align: center;
        }

        .td-dia {
            min-width: 140px;
            vertical-align: top;
            transition: 0.3s;
            border: 1px solid #eee !important;
            border-radius: 8px;
        }

        .td-passado {
            background-color: #efefef !important;
            opacity: 0.8;
        }

        .td-bloqueado {
            background: repeating-linear-gradient(45deg, #fdfdfd, #fdfdfd 10px, #f9f9f9 10px, #f9f9f9 20px);
        }

        /* Cards de Escala Compactos */
        .card-escala {
            font-size: 0.68rem;
            padding: 6px;
            border-radius: 6px;
            margin-bottom: 4px;
            border-left: 4px solid #0d6efd;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            cursor: pointer;
            transition: transform 0.1s;
        }

        .card-escala:active {
            transform: scale(0.98);
        }

        .status-confirmada {
            border-left-color: #198754;
            background-color: #f0fff4;
        }

        .status-atrasada {
            border-left-color: #ffc107;
            background-color: #fffbeb;
        }

        .status-ajuste {
            border-left-color: #dc3545;
            background-color: #fff5f5;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .btn-add {
            border: 1px dashed #ccc;
            color: #999;
            font-size: 0.7rem;
            padding: 4px;
            width: 100%;
            border-radius: 4px;
        }

        .btn-add:hover {
            border-color: #0d6efd;
            color: #0d6efd;
            background: #f0f7ff;
        }

        /* Status Confirmada - Bloqueado para Sócio */
        .card-escala.bloqueado {
            opacity: 0.7;
            cursor: not-allowed !important;
            background: repeating-linear-gradient(45deg, #f0fff4, #f0fff4 10px, #e8ffe8 10px, #e8ffe8 20px);
        }

        .card-escala.bloqueado:hover {
            transform: none !important;
        }
    </style>

    <div class="container-fluid p-3 mt-3">
        <div class="d-flex justify-content-between align-items-center mb-3 bg-white p-3 ">
            <div>
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center">
                    <i class="bi bi-calendar3-range-fill me-2 text-primary"></i>
                    PLANEJAMENTO DE ESCALAS
                </h4>
                <small class="text-muted fw-medium text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">
                    <i class="bi bi-person-badge me-1"></i> Gestão por Equipe 
                </small>
            </div>

            <div class="d-flex gap-2 align-items-center">
                <a href="{{ route('relatorios.escalas.canceladas') }}" class="btn btn-outline-danger btn-sm fw-bold px-3 shadow-sm border-2">
                    <i class="bi bi-clock-history me-1"></i> Históricos Cancelamentos
                </a>
            </div>
        </div>

        <div class="card-body bg-white p-3">
            <form action="{{ route('agenda_socio.index') }}" method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-bold text-secondary mb-1"> EMPRESA</label>
                    <select name="empresa_id" class="form-select border-2" onchange="this.form.submit()">
                        <option value="">Escolha um Empresa</option>
                        @foreach ($empresas_lista as $emp)
                            <option value="{{ $emp->id }}" {{ request('empresa_id') == $emp->id ? 'selected' : '' }}>
                                {{ strtoupper($emp->nome_fantasia) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-5 col-md-6">
                    <label class="form-label small fw-bold text-secondary mb-1">PERÍODO SEMANAL</label>
                    <div class="input-group">
                        <a href="{{ route('agenda_socio.index', ['data' => $inicioSemana->copy()->subWeek()->format('Y-m-d'), 'empresa_id' => request('empresa_id')]) }}"
                            class="btn btn-primary px-3">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                        <input type="date" name="data" class="form-control text-center fw-bold border-2"
                            value="{{ request('data', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
                        <a href="{{ route('agenda_socio.index', ['data' => $inicioSemana->copy()->addWeek()->format('Y-m-d'), 'empresa_id' => request('empresa_id')]) }}"
                            class="btn btn-primary px-3">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-12">
                    <div class="d-flex gap-2">
                        <a href="{{ route('agenda_socio.index') }}"
                            class="btn btn-light border fw-bold text-secondary w-100 py-2">LIMPAR</a>
                        <a href="{{ route('agenda_socio.index', ['data' => now()->format('Y-m-d')]) }}"
                            class="btn btn-warning fw-bold text-dark w-100 py-2">HOJE</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-agenda">
                    <thead>
                        <tr>
                            <th style="width: 200px; border-radius: 10px 0 0 8px;">
                                <i class="bi bi-building me-2"></i>Cliente:
                            </th>
                            @php
                                $hoje = \Carbon\Carbon::now()->startOfDay();
                                $diasPt = [
                                    0 => 'DOM',
                                    1 => 'SEG',
                                    2 => 'TER',
                                    3 => 'QUA',
                                    4 => 'QUI',
                                    5 => 'SEX',
                                    6 => 'SÁB',
                                ];
                            @endphp

                            {{-- Loop de 7 dias baseado na data de início da semana --}}
                            @for ($i = 0; $i < 7; $i++)
                                @php
                                    $dataColuna = $inicioSemana->copy()->addDays($i);
                                @endphp
                                <th class="{{ $dataColuna->isWeekend() ? 'text-danger' : '' }}">
                                    {{ $diasPt[$dataColuna->dayOfWeek] }} <br>
                                    <small class="text-white-50">{{ $dataColuna->format('d/m') }}</small>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empresas as $empresa)
                            <tr>
                                <td class="fw-bold p-2 small bg-white shadow-sm" style="border-radius: 8px 0 0 8px;">
                                    {{ Str::limit($empresa->nome_fantasia, 25) }}
                                </td>

                                @for ($i = 0; $i < 7; $i++)
                                    @php
                                        $dataCard = $inicioSemana->copy()->addDays($i)->startOfDay();
                                        $dataLoop = $dataCard->format('Y-m-d');
                                        $diaPassou = $dataCard->lt($hoje);

                                        // Mapeamento para os campos de contrato (seg, ter, qua...)
                                        $mapaContrato = [
                                            0 => 'dom',
                                            1 => 'seg',
                                            2 => 'ter',
                                            3 => 'qua',
                                            4 => 'qui',
                                            5 => 'sex',
                                            6 => 'sab',
                                        ];
                                        $colunaBanco = $mapaContrato[$dataCard->dayOfWeek];
                                        $diaContratado = $empresa->$colunaBanco ?? false;

                                        $escalasDoDia = collect($escalas[$empresa->id][$dataLoop] ?? []);
                                        $ativas = $escalasDoDia->where('status_cancelamento', '!=', 'cancelado');
                                        $canceladas = $escalasDoDia->where('status_cancelamento', 'cancelado');
                                    @endphp

                                    <td
                                        class="td-dia {{ $diaPassou ? 'td-passado' : '' }} {{ !$diaContratado ? 'td-bloqueado' : '' }} p-1">
                                        @if ($diaContratado)
                                            {{-- Usamos o forelse: ele faz o loop e, se estiver vazio, cai no @empty --}}
                                            @forelse ($ativas as $esc)
                                                @php
                                                    $conf = $esc->status_presenca === 'confirmada';
                                                    $ajuste = $esc->solicitou_ajuste == 1;
                                                    $atraso = $diaPassou && !$conf;

                                                    $n = explode(' ', $esc->professor->name);
                                                    $nome =
                                                        $n[0] . (isset($n[1]) ? ' ' . substr($n[1], 0, 1) . '.' : '');

                                                    $tip =
                                                        "<b>Prof:</b> {$esc->professor->name}<br><b>Tipo:</b> " .
                                                        strtoupper($esc->tipo_aula ?? 'Normal');
                                                    if ($atraso) {
                                                        $tip .= "<br><span class='text-warning'>⚠️ Esquecimento</span>";
                                                    }
                                                    if ($conf) {
                                                        $tip .=
                                                            "<br><span class='text-success'>✅ Aula Confirmada</span>";
                                                    }

                                                    $estaBloqueado = $conf && Auth::user()->perfil === 'socio';
                                                @endphp

                                                <div class="card-escala {{ $conf ? 'status-confirmada' : ($ajuste ? 'status-ajuste' : ($atraso ? 'status-atrasada' : '')) }} {{ $estaBloqueado ? 'bloqueado' : '' }}"
                                                    data-bs-toggle="tooltip" data-bs-html="true"
                                                    title="{!! $tip !!}"
                                                    @if (!$estaBloqueado) onclick="prepararAgendamento('{{ $empresa->id }}','{{ $empresa->nome_fantasia }}','{{ $dataLoop }}','{{ $esc->user_id }}','{{ $esc->valor_venda_avulso }}','{{ $esc->turno }}','{{ $esc->observacao }}','{{ $esc->status_cancelamento }}','{{ $esc->observacao_cancelamento }}',{{ $conf ? 'true' : 'false' }},{{ $ajuste ? 'true' : 'false' }},'{{ $esc->id }}','{{ $esc->tipo_aula ?? 'normal' }}')"
                 @else
                    onclick="event.stopPropagation(); alert('❌ Aula confirmada. Apenas administradores podem editá-la.');" @endif>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span><i
                                                                class="bi bi-clock me-1"></i>{{ strtoupper($esc->turno) }}</span>
                                                        <span class="fw-bold">{{ $nome }}</span>
                                                    </div>
                                                </div>
                                            @empty
                                                {{-- Se não houver escalas ativas no loop --}}
                                                <div class="text-center opacity-25 mt-2" style="font-size: 10px;">
                                                    SEM ESCALA
                                                </div>
                                            @endforelse

                                            {{-- Botão de Adicionar (Opcional - caso queira permitir criar nova escala no clique do vazio) --}}
                                            @if (!$diaPassou)
                                                <button class="btn-add btn btn-sm mt-1"
                                                    onclick="prepararAgendamento('{{ $empresa->id }}','{{ $empresa->nome_fantasia }}','{{ $dataLoop }}','','','','','','',false,false,'','')">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            @endif
                                        @else
                                            {{-- Dia Bloqueado --}}
                                            <div class="text-center mt-2 opacity-25" data-bs-toggle="tooltip"
                                                title="Dia fora do contrato do cliente.">
                                                <i class="bi bi-lock-fill"></i>
                                            </div>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('agenda.partials.modal_escala')
@endsection
