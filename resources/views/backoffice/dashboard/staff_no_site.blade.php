@extends('layouts.main')

@section('title', 'Aucun centre attribué')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 px-4">
                <div class="mb-3">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                          style="width:84px;height:84px;background:#fff4cc;color:#a06700;">
                        <i class="ti ti-lock f-32"></i>
                    </span>
                </div>
                <h4 class="mb-2">Accès limité</h4>
                <p class="text-muted mb-4">
                    Votre compte n'est rattaché à aucun centre GLS.<br>
                    Pour accéder au tableau de bord et aux données, contactez un administrateur.
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-user me-1"></i> Mon profil
                    </a>
                    <a href="mailto:contact@gls-sprachzentrum.ma" class="btn btn-primary">
                        <i class="ti ti-mail me-1"></i> Contacter l'administrateur
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
