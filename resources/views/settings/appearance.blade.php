@extends('layouts.app')
@section('title', 'Appearance')
@section('content')
    <div class="mb-6">
        <p class="text-sm font-medium text-[#01553d]">Account</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Appearance</h1>
        <p class="mt-1 text-sm text-slate-500">Choose a color theme for the app. Your choice is saved on this browser.</p>
    </div>

    <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="color-theme-heading">
        <h2 id="color-theme-heading" class="font-semibold text-slate-900">Color theme</h2>
        <p class="mt-1 text-sm text-slate-500">The selected colors are applied throughout the app.</p>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            @foreach([
                ['green', 'Kikimo Green', '#01553d', '#79c143'],
                ['blue', 'Ocean Blue', '#2563eb', '#60a5fa'],
                ['violet', 'Royal Violet', '#7c3aed', '#c4b5fd'],
                ['amber', 'Warm Amber', '#b45309', '#fbbf24'],
                ['rose', 'Rose', '#e11d48', '#fb7185'],
                ['teal', 'Teal', '#0f766e', '#2dd4bf'],
                ['indigo', 'Indigo', '#4f46e5', '#818cf8'],
                ['red', 'Crimson', '#dc2626', '#f87171'],
                ['slate', 'Slate', '#475569', '#94a3b8'],
                ['cyan', 'Cyan', '#0891b2', '#22d3ee'],
            ] as [$theme, $label, $primary, $secondary])
                <button type="button" data-color-theme-choice="{{ $theme }}" data-color-theme-label="{{ $label }}" aria-pressed="false" class="rounded-xl border-2 border-slate-200 p-4 text-left transition hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-offset-2">
                    <span class="flex items-center gap-2" aria-hidden="true">
                        <span class="h-5 w-5 rounded-full" style="background-color: {{ $primary }}"></span>
                        <span class="h-5 w-5 rounded-full" style="background-color: {{ $secondary }}"></span>
                    </span>
                    <span class="mt-3 block text-sm font-semibold text-slate-800">{{ $label }}</span>
                </button>
            @endforeach
        </div>
        <p id="color-theme-status" class="mt-4 text-sm text-slate-500" aria-live="polite"></p>
    </section>
@endsection
