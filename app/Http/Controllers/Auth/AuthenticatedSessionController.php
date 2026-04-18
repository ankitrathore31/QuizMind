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
            update_login_streak($user);

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
     public function destroy(Request $request)
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

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        // ✅ Check ONLY by email
        $existing = User::where('email', $googleUser->getEmail())->first();

        // ✅ If user exists → login directly
        if ($existing) {

            // Attach google_id if not already set
            if (!$existing->google_id) {
                $existing->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            }

            Auth::login($existing, true);
            update_login_streak($existing);
            $request->session()->regenerate();

            // ✅ Role-based redirect (same as your normal login)
            if ($existing->role === 'student') {
                return redirect()->route('student.dashboard');
            } elseif ($existing->role === 'teacher') {
                return redirect()->route('teacher.dashboard');
            } elseif ($existing->role === 'institution') {
                return redirect()->route('institution.dashboard');
            } elseif ($existing->role === 'parent') {
                return redirect()->route('parent.dashboard');
            }

            return redirect()->route('dashboard');
        }

        // ❗ New user → store in session
        $request->session()->put('google_pending', [
            'google_id' => $googleUser->getId(),
            'name'      => $googleUser->getName(),
            'email'     => $googleUser->getEmail(),
            'avatar'    => $googleUser->getAvatar(),
        ]);

        return redirect()->route('auth.google.role');
    }

    public function showRoleSelect(Request $request)
    {
        if (!$request->session()->has('google_pending')) {
            return redirect()->route('login'); // FIXED
        }

        $google = $request->session()->get('google_pending');
        return view('auth.google-role', compact('google'));
    }

    public function storeGoogleRole(Request $request)
    {
        $google = $request->session()->get('google_pending');

        if (!$google) {
            return redirect()->route('login'); // FIXED
        }

        $data = $request->validate([
            'role'     => ['required', 'in:student,teacher,institution,parent'],
            'college'  => ['nullable', 'string', 'max:255'],
            'ref_code' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name'              => $google['name'],
            'email'             => $google['email'],
            'password'          => Hash::make(Str::random(32)),
            'google_id'         => $google['google_id'],
            'avatar'            => $google['avatar'],
            'role'              => $data['role'],
            'college'           => $data['college'] ?? null,
            'ref_code'          => $data['ref_code'] ?? null,
            'email_verified_at' => now(),
        ]);

        // ✅ If student → create profile (same as manual register)
        if ($user->role === 'student') {
            $user->student()->create([
                'level' => 1,
                'xp' => 0,
                'streak' => 0,
                'total_quizzes' => 0,
                'total_correct' => 0,
                'total_wrong' => 0,
                'total_battles_won' => 0,
                'total_battles_lost' => 0,
                'badges' => [],
                'subjects_interest' => [],
                'is_profile_complete' => false,
            ]);
        }

        $request->session()->forget('google_pending');

        Auth::login($user, true);
        update_login_streak($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('status', 'Account created! Welcome, ' . $user->name . ' 🎉');
    }
}
