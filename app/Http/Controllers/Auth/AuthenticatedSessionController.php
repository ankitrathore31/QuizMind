<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthenticatedSessionController extends Controller
{
    // Show login page
    public function create()
    {
        $mode = old('name') ? 'register' : 'login';
        return view('auth.login', compact('mode'));
    }

    // Handle login (IMPORTANT: must be 'store')
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Role-based redirection
            if ($user->role === 'student') {
                return redirect()->route('student.dashboard')
                    ->with('status', 'Welcome back, ' . $user->name . '! 🎓');
            } elseif ($user->role === 'teacher') {
                return redirect()->route('teacher.dashboard')
                    ->with('status', 'Welcome back, ' . $user->name . '! 👨‍🏫');
            } elseif ($user->role === 'institution') {
                return redirect()->route('institution.dashboard')
                    ->with('status', 'Welcome back, ' . $user->name . '! 🏫');
            } elseif ($user->role === 'parent') {
                return redirect()->route('parent.dashboard')
                    ->with('status', 'Welcome back, ' . $user->name . '! 👨‍👩‍👧');
            }

            // fallback (optional)
            return redirect()->route('dashboard')
                ->with('status', 'Welcome back, ' . $user->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }


    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // ──────────────────────────────────────────────────────
    //  Google OAuth — Callback
    //
    //  FLOW:
    //  • Returning user  → log in directly → dashboard
    //  • Brand new user  → store google data in session
    //                     → redirect to role-selection popup
    // ──────────────────────────────────────────────────────
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('auth')
                ->withErrors(['email' => 'Google login failed. Please try again.']);
        }

        // --- Existing user? Just log them in ---
        $existing = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($existing) {
            // Keep google_id and avatar up to date
            $existing->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $googleUser->getAvatar(),
            ]);

            Auth::login($existing, true);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('status', 'Welcome back, ' . $existing->name . '! 🎉');
        }

        // --- New user: save Google data in session, show role popup ---
        $request->session()->put('google_pending', [
            'google_id' => $googleUser->getId(),
            'name'      => $googleUser->getName(),
            'email'     => $googleUser->getEmail(),
            'avatar'    => $googleUser->getAvatar(),
        ]);

        return redirect()->route('auth.google.role');
    }

    // ──────────────────────────────────────────────────────
    //  Show role-selection popup page (Google new users)
    // ──────────────────────────────────────────────────────
    public function showRoleSelect(Request $request)
    {
        // Guard: must have pending google data in session
        if (!$request->session()->has('google_pending')) {
            return redirect()->route('auth');
        }

        $google = $request->session()->get('google_pending');
        return view('auth.google-role', compact('google'));
    }

    // ──────────────────────────────────────────────────────
    //  Handle role-selection form submit (Google new users)
    // ──────────────────────────────────────────────────────
    public function storeGoogleRole(Request $request)
    {
        // Guard: must have pending google data in session
        $google = $request->session()->get('google_pending');
        if (!$google) {
            return redirect()->route('auth');
        }

        $data = $request->validate([
            'role'     => ['required', 'in:student,teacher,institution,parent'],
            'college'  => ['nullable', 'string', 'max:255'],
            'ref_code' => ['nullable', 'string', 'max:50'],
        ]);

        // Create the user now with all data
        $user = User::create([
            'name'              => $google['name'],
            'email'             => $google['email'],
            'password'          => Hash::make(Str::random(32)), // never used
            'google_id'         => $google['google_id'],
            'avatar'            => $google['avatar'],
            'role'              => $data['role'],
            'college'           => $data['college']  ?? null,
            'ref_code'          => $data['ref_code'] ?? null,
            'email_verified_at' => now(), // Google accounts are pre-verified
        ]);

        // Clear session pending data
        $request->session()->forget('google_pending');

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Account created! Welcome, ' . $user->name . ' 🎉');
    }
}
