<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role'     => ['required', 'in:student,teacher,institution,parent'],
            'college'  => ['nullable', 'string', 'max:255'],
            'ref_code' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
            'college'  => $data['college'] ?? null,
            'ref_code' => $data['ref_code'] ?? null,
        ]);

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

        if ($user->role === 'institution') {

            $collegeName = $data['college'] ?? $user->name;

            // Generate code
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $collegeName), 0, 3));
            $random = strtoupper(Str::random(3)) . rand(100, 999);
            $code = $prefix . $random;

            Institution::create([
                'user_id' => $user->id,
                'name'    => $collegeName,
                'code'    => $code,
                'email'   => $user->email,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->back()
            ->with('status', 'Register Successfully Please login, ' . $user->name . '! 🚀');
    }
}
