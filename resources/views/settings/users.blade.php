@extends('layouts.app')
@section('title', 'Team members')
@section('content')
    <div class="mb-6">
        <p class="text-sm font-medium text-[#01553d]">Workspace access</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Team members</h1>
        <p class="mt-1 text-sm text-slate-500">Login details and terminal access for {{ auth()->user()->business->name }}.</p>
    </div>
    @if($terminals->isEmpty())
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Register a POS terminal before adding or assigning team accounts.</div>
    @endif
    <div class="grid gap-5 xl:grid-cols-3">
        <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm xl:col-span-2">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="font-semibold text-slate-900">User accounts</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $users->total() }} accounts · Passwords are never displayed.</p>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($users as $managedUser)
                    <article class="space-y-4 px-5 py-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 font-semibold text-[#01553d]">{{ strtoupper(substr($managedUser->name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-800">{{ $managedUser->name }} @if($managedUser->is(auth()->user()))<span class="text-xs font-normal text-slate-400">(you)</span>@endif</p>
                                    <p class="truncate text-sm text-slate-500">{{ $managedUser->email }}</p>
                                    @if($managedUser->phone)<p class="truncate text-xs text-slate-400">{{ $managedUser->phone }}</p>@endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $managedUser->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $managedUser->is_active ? 'Active' : 'Disabled' }}</span>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold capitalize text-slate-600">{{ $managedUser->role }}</span>
                            </div>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Assigned machines</p>
                            @if($managedUser->posTerminals->isNotEmpty())
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach($managedUser->posTerminals as $assignedTerminal)
                                        <span class="rounded-lg bg-white px-2.5 py-1.5 text-xs text-slate-700 ring-1 ring-slate-200">
                                            <span class="font-medium">{{ $assignedTerminal->terminal_name }}</span>
                                            <span class="ml-1 font-mono text-slate-500">{{ $assignedTerminal->machine_id }}</span>
                                            @if(! $assignedTerminal->is_active)<span class="ml-1 text-amber-700">(inactive)</span>@endif
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-1 text-sm text-amber-700">{{ $managedUser->role === 'owner' ? 'Business-wide owner access' : 'Not assigned' }}</p>
                            @endif
                        </div>
                        @if($managedUser->role !== 'owner')
                            <form method="POST" action="{{ route('users.update', $managedUser) }}" class="grid gap-3 sm:grid-cols-2">
                                @csrf @method('PATCH')
                                <input type="hidden" name="pos_terminal_ids_present" value="1">
                                <div>
                                    <label for="role-{{ $managedUser->id }}" class="mb-1 block text-xs font-medium text-slate-500">Role</label>
                                    <select id="role-{{ $managedUser->id }}" name="role" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                        <option value="staff" @selected($managedUser->role === 'staff')>Staff</option>
                                        <option value="admin" @selected($managedUser->role === 'admin')>Administrator</option>
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="mb-1 block text-xs font-medium text-slate-500">Assigned POS machines</p>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        @foreach($terminals as $terminal)
                                            <label class="flex min-w-0 items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                                <input type="checkbox" name="pos_terminal_ids[]" value="{{ $terminal->id }}" @checked($managedUser->posTerminals->contains('id', $terminal->id)) class="rounded border-slate-300 text-[#01553d] focus:ring-[#01553d]">
                                                <span class="min-w-0 truncate">{{ $terminal->terminal_name }} <span class="font-mono text-xs text-slate-500">{{ $terminal->machine_id }}</span>{{ $terminal->is_active ? '' : ' (inactive)' }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('pos_terminal_ids')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="active-{{ $managedUser->id }}" class="mb-1 block text-xs font-medium text-slate-500">Account status</label>
                                    <select id="active-{{ $managedUser->id }}" name="is_active" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                        <option value="1" @selected($managedUser->is_active)>Active</option>
                                        <option value="0" @selected(! $managedUser->is_active)>Disabled</option>
                                    </select>
                                </div>
                                <div class="flex items-end justify-end">
                                    <button class="rounded-lg bg-[#01553d] px-4 py-2 text-xs font-semibold text-white hover:bg-[#0a2a1d]">Save account</button>
                                </div>
                            </form>
                            <details class="rounded-xl border border-slate-200">
                                <summary class="cursor-pointer px-3 py-2.5 text-sm font-semibold text-[#01553d]">Set a new password</summary>
                                <form method="POST" action="{{ route('users.update', $managedUser) }}" class="grid gap-3 border-t border-slate-100 p-3 sm:grid-cols-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="role" value="{{ $managedUser->role }}">
                                    @foreach($managedUser->posTerminals->where('is_active', true) as $assignedTerminal)
                                        <input type="hidden" name="pos_terminal_ids[]" value="{{ $assignedTerminal->id }}">
                                    @endforeach
                                    <input type="hidden" name="is_active" value="{{ $managedUser->is_active ? 1 : 0 }}">
                                    <div><label for="password-{{ $managedUser->id }}" class="mb-1 block text-xs font-medium text-slate-500">New password</label><input id="password-{{ $managedUser->id }}" name="password" type="password" minlength="6" required autocomplete="new-password" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                                    <div><label for="password-confirm-{{ $managedUser->id }}" class="mb-1 block text-xs font-medium text-slate-500">Confirm password</label><input id="password-confirm-{{ $managedUser->id }}" name="password_confirmation" type="password" minlength="6" required autocomplete="new-password" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                                    <p class="text-xs leading-5 text-slate-500 sm:col-span-2">The current password cannot be viewed. Saving a new password signs this user out of existing sessions.</p>
                                    <div class="sm:col-span-2 sm:text-right"><button class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Update password</button></div>
                                </form>
                            </details>
                            <div class="text-right">
                                <form method="POST" action="{{ route('users.destroy', $managedUser) }}" onsubmit="return confirm('Remove this team member?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Remove account</button>
                                </form>
                            </div>
                        @else
                            <p class="text-xs leading-5 text-slate-400">The owner account has business-wide access and cannot be changed here.</p>
                        @endif
                    </article>
                @empty
                    <p class="px-5 py-12 text-center text-sm text-slate-400">No team members found.</p>
                @endforelse
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $users->links() }}</div>
        </section>
        <form method="POST" action="{{ route('users.store') }}" class="h-fit rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            @csrf
            <h2 class="font-semibold text-slate-900">Add a user account</h2>
            <p class="mt-1 text-sm text-slate-500">Staff can access the POS machines assigned to their account.</p>
            <div class="mt-5 space-y-4">
                <div><label for="name" class="mb-1 block text-sm font-medium text-slate-700">Full name</label><input id="name" name="name" required value="{{ old('name') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></div>
                <div><label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Phone</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></div>
                <div><label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email login</label><input id="email" name="email" type="email" required value="{{ old('email') }}" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></div>
                <div><label for="role" class="mb-1 block text-sm font-medium text-slate-700">Role</label><select id="role" name="role" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"><option value="staff">Staff</option><option value="admin">Administrator</option></select></div>
                <fieldset>
                    <legend class="mb-1 block text-sm font-medium text-slate-700">Assigned POS machines</legend>
                    <div class="max-h-48 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3">
                        @foreach($terminals as $terminal)
                            <label class="flex min-w-0 items-center gap-2 text-sm">
                                <input type="checkbox" name="pos_terminal_ids[]" value="{{ $terminal->id }}" @checked(in_array($terminal->id, (array) old('pos_terminal_ids', []), true)) class="rounded border-slate-300 text-[#01553d] focus:ring-[#01553d]">
                                <span class="min-w-0 truncate">{{ $terminal->terminal_name }} <span class="font-mono text-xs text-slate-500">{{ $terminal->machine_id }}</span>{{ $terminal->is_active ? '' : ' (inactive)' }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Staff need at least one active machine. Assign multiple machines when needed.</p>
                    @error('pos_terminal_ids')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    @error('pos_terminal_ids.*')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </fieldset>
                <div><label for="password" class="mb-1 block text-sm font-medium text-slate-700">Initial password</label><input id="password" name="password" type="password" minlength="6" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"><p class="mt-1 text-xs text-slate-400">At least 6 characters. Passwords cannot be viewed later.</p></div>
                <div><label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="6" required autocomplete="new-password" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm"></div>
                <button @disabled($terminals->isEmpty()) class="w-full rounded-xl bg-[#01553d] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#0a2a1d] disabled:cursor-not-allowed disabled:opacity-50">Add user</button>
            </div>
        </form>
    </div>
@endsection
