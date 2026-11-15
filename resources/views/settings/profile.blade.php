@extends('layouts.app')
@section('title', 'My profile')
@section('content')
    <div class="mx-auto max-w-2xl space-y-6">
        <div>
            <p class="text-sm font-semibold text-[#01553d]">Account</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">My profile</h1>
            <p class="mt-2 text-sm text-slate-500">Update the name, phone, and email associated with your account.</p>
        </div>
        @include('partials.flash')
        <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm sm:p-7">
            <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
                    <input id="name" name="name" required value="{{ old('name', $user->name) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#2b805b] focus:outline-none">
                    @error('name')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-slate-700">Phone</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#2b805b] focus:outline-none">
                    @error('phone')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email / login</label>
                    <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-[#2b805b] focus:outline-none">
                    @error('email')<p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end border-t border-slate-100 pt-5">
                    <button class="rounded-xl bg-[#01553d] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#0a2a1d]">Save profile</button>
                </div>
            </form>
        </section>
    </div>
@endsection
