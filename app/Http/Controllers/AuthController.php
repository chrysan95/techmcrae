<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate the incoming request
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // 2. Determine if the user typed an email or an Employee ID
        $loginType = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_id';
        $loginValue = $request->input('login');
        $passwordValue = $request->input('password');

        // 3. Manually search the database for a matching record
        $user = Employee::where($loginType, $loginValue)->first();

        if ($user
            && str_starts_with((string) $user->password, '$2y$')
            && Hash::check($passwordValue, $user->password)) {

            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();
            Log::info('Login success', ['employee_id' => $user->employee_id, 'role' => $user->role]);

            $destination = $user->role === 'admin' ? '/dashboard' : '/portal';
            return redirect()->intended($destination)->with('login_success', true);
        }

        Log::warning('Login failed', ['login' => $loginValue, 'ip' => $request->ip()]);

        // 5. If no user is found, send them back with an error
        return back()->withErrors([
            'login' => 'Invalid credentials.',
        ])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
         Log::info('Logout', ['employee_id' => Auth::user()?->employee_id]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}