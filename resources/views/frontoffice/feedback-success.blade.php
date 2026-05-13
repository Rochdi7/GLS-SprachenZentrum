@extends('frontoffice.layouts.app')

@section('title', __('feedback.success_page_title'))

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/frontoffice/ressource/attestation-request.css') }}">

@php
    $successText = explode('|', __('feedback.success_text'));
@endphp

@section('content')
<main class="att-success-page">
    <div class="container">
        <div class="att-success-card">
            <div class="icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h1>{{ __('feedback.success_heading') }}</h1>
            <p>
                {{ $successText[0] ?? '' }}<br>
                {{ $successText[1] ?? '' }}
            </p>
            <div class="att-success-actions">
                <a href="{{ LaravelLocalization::localizeUrl(route('front.home')) }}" class="att-btn-primary">
                    <i class="bi bi-house-door"></i> {{ __('feedback.success_back_home') }}
                </a>
                <a href="{{ LaravelLocalization::localizeUrl(route('front.contact')) }}" class="att-btn-ghost">
                    <i class="bi bi-chat-dots"></i> {{ __('feedback.success_contact') }}
                </a>
            </div>
        </div>
    </div>
</main>
@endsection
