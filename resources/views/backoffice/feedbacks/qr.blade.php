@extends('layouts.main')

@section('title', 'QR Code — Avis étudiants')
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-link', route('backoffice.feedbacks.index'))
@section('breadcrumb-item-active', 'QR code à partager')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        :root {
            --gls-orange: #ff6b35;
            --gls-orange-2: #ff8c42;
            --gls-ink: #1a1a1a;
        }

        .qr-stage {
            display: flex;
            justify-content: center;
            padding: 40px 24px;
        }

        /* ── Animated-border card ─────────────────────────── */
        .qr-card-anim {
            position: relative;
            width: 100%;
            max-width: 460px;
            padding: 6px;            /* thickness of the moving border */
            border-radius: 28px;
            background: #f4f5f7;
            overflow: hidden;
            isolation: isolate;
        }

        /* The rotating conic-gradient ribbon */
        .qr-card-anim::before {
            content: "";
            position: absolute;
            inset: -50%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg,
                var(--gls-orange) 60deg,
                var(--gls-orange-2) 110deg,
                transparent 170deg,
                transparent 190deg,
                var(--gls-orange) 250deg,
                var(--gls-orange-2) 300deg,
                transparent 360deg
            );
            animation: qr-spin 4s linear infinite;
            z-index: 0;
        }

        /* Soft glow that pulses with the ribbon */
        .qr-card-anim::after {
            content: "";
            position: absolute;
            inset: -4px;
            border-radius: 32px;
            background: radial-gradient(circle at 50% 0%, rgba(255,107,53,.35), transparent 60%);
            filter: blur(14px);
            opacity: .55;
            z-index: -1;
            animation: qr-glow 4s ease-in-out infinite;
        }

        @keyframes qr-spin {
            to { transform: rotate(1turn); }
        }

        @keyframes qr-glow {
            0%, 100% { opacity: .45; }
            50%      { opacity: .8;  }
        }

        /* Inner panel sits on top of the rotating ribbon */
        .qr-card-inner {
            position: relative;
            z-index: 1;
            background: #ffffff;
            border-radius: 22px;
            padding: 32px 28px 28px;
            text-align: center;
        }

        .qr-brand {
            font-weight: 800;
            font-size: 1.45rem;
            letter-spacing: -.01em;
            color: var(--gls-ink);
            margin-bottom: 6px;
        }

        .qr-brand .accent {
            color: var(--gls-orange);
        }

        .qr-sub {
            color: #6b7280;
            margin-bottom: 22px;
            font-size: .95rem;
        }

        .qr-frame {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 14px;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 6px 22px rgba(0,0,0,.08);
            margin-bottom: 20px;
        }

        .qr-frame svg {
            width: 280px;
            height: 280px;
            max-width: 100%;
            display: block;
        }

        .qr-cta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 999px;
            background: var(--gls-ink);
            color: #fff;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .qr-cta .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gls-orange);
            box-shadow: 0 0 0 0 rgba(255,107,53,.7);
            animation: qr-pulse 1.6s ease-out infinite;
        }

        @keyframes qr-pulse {
            0%   { box-shadow: 0 0 0 0   rgba(255,107,53,.7); }
            70%  { box-shadow: 0 0 0 10px rgba(255,107,53,0);  }
            100% { box-shadow: 0 0 0 0   rgba(255,107,53,0);   }
        }

        .qr-url-box {
            margin-top: 22px;
            padding: 10px 14px;
            background: #f8f9fa;
            border: 1px dashed #d1d5db;
            border-radius: 10px;
            font-family: ui-monospace, 'SF Mono', Menlo, monospace;
            font-size: .82rem;
            color: #374151;
            word-break: break-all;
        }

        /* Reduced-motion users — kill the spinning ribbon */
        @media (prefers-reduced-motion: reduce) {
            .qr-card-anim::before { animation: none; background: var(--gls-orange); }
            .qr-card-anim::after  { animation: none; }
            .qr-cta .dot           { animation: none; }
        }

        /* Print: clean, no animation, single page */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .card { border: none !important; box-shadow: none !important; }
            .qr-stage { padding: 0; }
            .qr-card-anim::before,
            .qr-card-anim::after { display: none; }
            .qr-card-anim {
                border: 2px solid var(--gls-ink);
                padding: 0;
                background: #fff;
            }
            .qr-card-inner { box-shadow: none; }
            .qr-cta .dot { animation: none; }
        }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center no-print">
                    <h5 class="mb-0"><i class="ti ti-qrcode me-1"></i> QR Code — Formulaire d'avis étudiants</h5>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-sm btn-primary">
                            <i class="ti ti-printer me-1"></i> Imprimer
                        </button>
                        <a href="{{ route('backoffice.feedbacks.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Retour
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="qr-stage">
                        <div class="qr-card-anim">
                            <div class="qr-card-inner">
                                <div class="qr-cta no-print">
                                    <span class="dot"></span> Scannez-moi
                                </div>

                                <div class="qr-brand mt-3">
                                    GLS <span class="accent">Sprachenzentrum</span>
                                </div>
                                <div class="qr-sub">
                                    Partagez votre avis en un scan
                                </div>

                                <div class="qr-frame">
                                    {!! $qrSvg !!}
                                </div>

                                <div class="qr-sub mb-0" style="font-size:.85rem">
                                    Pointez l'appareil photo de votre téléphone vers ce code
                                </div>

                                <div class="qr-url-box no-print">
                                    {{ $url }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center text-muted no-print pb-4 px-4">
                        <small>
                            <i class="ti ti-info-circle me-1"></i>
                            Imprimez cette page et affichez-la dans vos centres GLS. Les étudiants pourront scanner le code pour accéder directement au formulaire d'avis.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
