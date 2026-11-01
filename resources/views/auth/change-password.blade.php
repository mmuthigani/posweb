@extends('layouts.app')

@section('title', 'Change password')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <p class="text-sm font-semibold text-[#01553d]">Account security</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Change password</h1>
            <p class="mt-2 text-sm text-slate-500">Choose a new password with at least 12 characters.</p>
        </div>

        @include('partials.flash')

        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
            <form method="POST" action="{{ route('account.password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="mb-1.5 block text-sm font-medium text-slate-700">Current password</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#2b805b] focus:outline-none focus:ring-4 focus:ring-emerald-900/5">
                    @error('current_password')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">New password</label>
                    <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#2b805b] focus:outline-none focus:ring-4 focus:ring-emerald-900/5">
                    @error('password')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#2b805b] focus:outline-none focus:ring-4 focus:ring-emerald-900/5">
                </div>

                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button class="rounded-xl bg-[#01553d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0a2a1d]">Save new password</button>
                </div>
            </form>
        </section>
    </div>
@endsection
