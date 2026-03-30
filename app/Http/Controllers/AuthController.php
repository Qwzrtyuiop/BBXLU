<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function createRegister(): View
    {
        return view('auth.register', [
            'unclaimedAccounts' => $this->unclaimedAccounts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);
        $password = $credentials['password'];

        $attempted = Auth::attempt(['nickname' => $login, 'password' => $password])
            || Auth::attempt(['email' => $login, 'password' => $password]);

        if (! $attempted) {
            return back()->withErrors([
                'login' => 'Invalid credentials.',
            ])->onlyInput('login');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if (! $user) {
            return back()->withErrors([
                'login' => 'Unable to sign you in right now.',
            ])->onlyInput('login');
        }

        return redirect()->intended(
            $user->role === 'admin' ? route('dashboard') : route('user.dashboard')
        );
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        $mode = $request->input('mode') === 'claim' ? 'claim' : 'register';

        if ($mode === 'claim') {
            $claimLookup = $request->validate([
                'mode' => ['required', 'in:register,claim'],
                'claim_nickname' => ['required', 'string', 'max:255'],
            ]);

            $claimNickname = trim($claimLookup['claim_nickname']);
            $user = $this->findUnclaimedAccount($claimNickname);

            if (! $user) {
                return back()
                    ->withErrors([
                        'claim_nickname' => 'No unclaimed account matched that nickname. Type the same nickname that was registered for you.',
                    ])
                    ->withInput($this->safeRegisterInput($request, [
                        'mode' => 'claim',
                        'claim_nickname' => $claimNickname,
                    ]));
            }

            $data = $request->validate([
                'mode' => ['required', 'in:register,claim'],
                'claim_nickname' => ['required', 'string', 'max:255'],
                'name' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user->fill([
                'name' => trim((string) ($data['name'] ?: $user->nickname)),
                'email' => $data['email'] ?: null,
                'password' => $data['password'],
                'is_claimed' => true,
            ])->save();

            Player::query()->firstOrCreate([
                'user_id' => $user->id,
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('user.dashboard')->with('status', 'Account claimed successfully.');
        }

        $data = $request->validate([
            'mode' => ['required', 'in:register,claim'],
            'nickname' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $nickname = trim($data['nickname']);
        $existingAccount = $this->findAccountByNickname($nickname);

        if ($existingAccount) {
            if (! $existingAccount->is_claimed) {
                return back()
                    ->withErrors([
                        'nickname' => 'This nickname already exists as an unclaimed account. Use Claim Account instead.',
                    ])
                    ->withInput($this->safeRegisterInput($request, [
                        'mode' => 'claim',
                        'claim_nickname' => $nickname,
                    ]));
            }

            return back()
                ->withErrors([
                    'nickname' => 'This nickname is already registered. Log in or choose another nickname.',
                ])
                ->withInput($this->safeRegisterInput($request));
        }

        $user = User::query()->create([
            'nickname' => $nickname,
            'name' => trim((string) ($data['name'] ?: $nickname)),
            'email' => $data['email'] ?: null,
            'password' => $data['password'],
            'role' => 'user',
            'is_claimed' => true,
        ]);

        Player::query()->create([
            'user_id' => $user->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('user.dashboard')->with('status', 'Registration complete.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function unclaimedAccounts()
    {
        return User::query()
            ->where('is_claimed', false)
            ->orderByRaw('LOWER(nickname)')
            ->get(['id', 'nickname']);
    }

    private function findUnclaimedAccount(string $nickname): ?User
    {
        if ($nickname === '') {
            return null;
        }

        return User::query()
            ->where('is_claimed', false)
            ->whereRaw('LOWER(nickname) = ?', [Str::lower($nickname)])
            ->first();
    }

    private function findAccountByNickname(string $nickname): ?User
    {
        if ($nickname === '') {
            return null;
        }

        return User::query()
            ->whereRaw('LOWER(nickname) = ?', [Str::lower($nickname)])
            ->first();
    }

    private function safeRegisterInput(Request $request, array $overrides = []): array
    {
        return array_merge(
            $request->except(['password', 'password_confirmation']),
            $overrides
        );
    }
}
