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
            ->with('posTerminal')
            ->orderBy('name')
            ->paginate(15);
        $terminals = PosTerminal::query()
            ->where('business_id', Auth::user()->business_id)
            ->orderBy('terminal_name')
            ->get();

        return view('settings.users', compact('users', 'terminals'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'pos_terminal_id' => [
                'required',
                'integer',
                Rule::exists('pos_terminals', 'id')
                    ->where('business_id', Auth::user()->business_id)
                    ->where('is_active', true),
            ],
        ]);
        User::query()->create([
            ...$validated,
            'business_id' => Auth::user()->business_id,
            'is_active' => true,
        ]);

        return back()->with('success', 'Team member added.');
    }

    public function updateUser(Request $request, User $managedUser): RedirectResponse
    {
        $this->authorizeUser($managedUser);
        abort_if($managedUser->role === 'owner', 403, 'Owner access cannot be changed here.');
        abort_if(Auth::user()->role !== 'owner' && $managedUser->role === 'admin', 403);
        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'staff'])],
            'is_active' => ['required', 'boolean'],
            'pos_terminal_id' => [
                'required',
                'integer',
                Rule::exists('pos_terminals', 'id')
                    ->where('business_id', Auth::user()->business_id)
                    ->where('is_active', true),
            ],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
        ]);
        if (! filled($validated['password'] ?? null)) {
            unset($validated['password']);
        }
        $managedUser->update($validated);

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
