<?php

namespace App\Http\Controllers;

use App\Models\PosTerminal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function appearance(): View
    {
        return view('settings.appearance');
    }

    public function profile(): View
    {
        return view('settings.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);
        $user->update($validated);

        return back()->with('success', 'Your profile has been updated.');
    }

    public function editBusiness(): View
    {
        return view('settings.business', ['business' => Auth::user()->business]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $business = Auth::user()->business;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
        ]);
        $validated['currency'] = Str::upper($validated['currency']);
        $business->update($validated);

        return back()->with('success', 'Business settings updated.');
    }

    public function users(): View
    {
        $users = User::query()
            ->where('business_id', Auth::user()->business_id)
            ->with('posTerminals')
            ->orderBy('name')
            ->paginate(15);
        $terminals = PosTerminal::query()
            ->where('business_id', Auth::user()->business_id)
            ->where('is_active', true)
            ->orderBy('terminal_name')
            ->get();

        return view('settings.users', compact('users', 'terminals'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        if ($request->filled('pos_terminal_id') && ! $request->exists('pos_terminal_ids')) {
            $request->merge(['pos_terminal_ids' => [$request->input('pos_terminal_id')]]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'pos_terminal_ids' => ['required_if:role,staff', 'array', 'min:1'],
            'pos_terminal_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('pos_terminals', 'id')
                    ->where('business_id', Auth::user()->business_id)
                    ->where('is_active', true),
            ],
        ]);
        $terminalIds = $validated['pos_terminal_ids'] ?? [];
        unset($validated['pos_terminal_ids']);
        $user = User::query()->create([
            ...$validated,
            'business_id' => Auth::user()->business_id,
            'pos_terminal_id' => $terminalIds[0] ?? null,
            'is_active' => true,
        ]);
        $user->posTerminals()->sync($terminalIds);

        return back()->with('success', 'Team member added.');
    }

    public function updateUser(Request $request, User $managedUser): RedirectResponse
    {
        $this->authorizeUser($managedUser);
        abort_if($managedUser->role === 'owner', 403, 'Owner access cannot be changed here.');
        abort_if(Auth::user()->role !== 'owner' && $managedUser->role === 'admin', 403);
        if ($request->filled('pos_terminal_id') && ! $request->exists('pos_terminal_ids')) {
            $request->merge(['pos_terminal_ids' => [$request->input('pos_terminal_id')]]);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'is_active' => ['required', 'boolean'],
            'pos_terminal_ids_present' => ['sometimes', 'accepted'],
            'pos_terminal_ids' => ['required_if:role,staff', 'array', 'min:1'],
            'pos_terminal_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('pos_terminals', 'id')
                    ->where('business_id', Auth::user()->business_id)
                    ->where('is_active', true),
            ],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);
        $terminalIds = $validated['pos_terminal_ids'] ?? [];
        $hasTerminalAssignments = $request->boolean('pos_terminal_ids_present')
            || array_key_exists('pos_terminal_ids', $request->all());
        unset($validated['pos_terminal_ids']);
        unset($validated['pos_terminal_ids_present']);
        if (! filled($validated['password'] ?? null)) {
            unset($validated['password']);
        }
        if ($hasTerminalAssignments) {
            $validated['pos_terminal_id'] = $terminalIds[0] ?? null;
        }
        $managedUser->update($validated);
        if ($hasTerminalAssignments) {
            $managedUser->posTerminals()->sync($terminalIds);
        }

        if (array_key_exists('password', $validated) && config('session.driver') === 'database') {
            DB::table(config('session.table'))->where('user_id', $managedUser->id)->delete();
        }

        return back()->with('success', 'Team member updated.');
    }

    public function deleteUser(User $managedUser): RedirectResponse
    {
        $this->authorizeUser($managedUser);
        abort_if($managedUser->role === 'owner', 403, 'Owner accounts cannot be removed here.');
        abort_if(Auth::user()->role !== 'owner' && $managedUser->role === 'admin', 403);
        abort_if($managedUser->is(Auth::user()), 422, 'You cannot remove your own account.');
        $managedUser->delete();

        return back()->with('success', 'Team member removed.');
    }

    public function newTerminal(): View
    {
        return view('terminals.create');
    }

    public function storeTerminal(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'string', 'max:100', 'unique:pos_terminals,machine_id'],
            'client_id' => ['required', 'string', 'max:100'],
            'terminal_name' => ['required', 'string', 'max:255'],
        ]);
        $token = Str::random(64);
        $terminal = PosTerminal::query()->create([
            ...$validated,
            'business_id' => Auth::user()->business_id,
            'api_token_hash' => Hash::make($token),
            'is_active' => true,
        ]);

        return redirect()->route('terminals.index')
            ->with('success', 'Terminal registered. Bearer authentication is optional; the token can still be used for stronger request verification.')
            ->with('terminal_token', $token)
            ->with('terminal_name', $terminal->terminal_name);
    }

    public function toggleTerminal(PosTerminal $terminal): RedirectResponse
    {
        abort_unless($terminal->business_id === Auth::user()->business_id, 404);
        $terminal->update(['is_active' => ! $terminal->is_active]);

        return back()->with('success', 'Terminal status updated.');
    }

    public function rotateTerminalToken(PosTerminal $terminal): RedirectResponse
    {
        abort_unless($terminal->business_id === Auth::user()->business_id, 404);
        $token = Str::random(64);
        $terminal->update(['api_token_hash' => Hash::make($token)]);

        return back()
            ->with('success', 'Bearer token rotated. The old token is no longer valid.')
            ->with('terminal_token', $token)
            ->with('terminal_name', $terminal->terminal_name);
    }

    private function authorizeUser(User $managedUser): void
    {
        abort_unless($managedUser->business_id === Auth::user()->business_id, 404);
    }
}
