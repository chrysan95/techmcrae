<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

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

        // 3. Manually search the database for a matching record (Plain Text Password Check)
        $user = Employee::where($loginType, $loginValue)
                        ->where('password', $passwordValue)
                        ->first();

        // 4. If a user is found, log them in manually
        if ($user) {
            Auth::login($user, $request->has('remember'));
            $request->session()->regenerate();
            
            // Determine where they go, and flash a success flag for the CSS fade-out animation
            $destination = $user->role === 'admin' ? '/dashboard' : '/portal';
            return redirect()->intended($destination)->with('login_success', true);
        }

        // 5. If no user is found, send them back with an error
        return back()->withErrors([
            'login' => 'Invalid credentials.',
        ])->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}