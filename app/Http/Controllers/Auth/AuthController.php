<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use LogsActivity;


    public function showRegistrationForm()
    {
        return view('auth.register');
    }


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'terms' => 'required|accepted'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        // Log registration
        $this->logActivity(
            'registered',
            'auth',
            "New user registered: {$user->name} ({$user->email})",
            null,
            $user->toArray(),
            null,
            'success'
        );

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome to ShopHub! Your account has been created successfully.');
    }


    public function showLoginForm()
    {
        return view('auth.login');
    }


    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            // Log successful login
            $this->logActivity(
                'login',
                'auth',
                "User logged in successfully: {$user->name} ({$user->email})",
                null,
                ['user_id' => $user->id, 'user_email' => $user->email],
                ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
                'success'
            );

            if ($user->is_admin) {
                return redirect()->route('admin.dashboard')
                    ->with('success', 'Welcome back, Administrator!');
            }
            
            return redirect()->route('dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');
        }

        // Log failed login attempt
        $this->logActivity(
            'login_failed',
            'auth',
            "Failed login attempt for email: {$request->email}",
            null,
            null,
            ['ip' => $request->ip(), 'user_agent' => $request->userAgent()],
            'failed'
        );

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }


    public function logout(Request $request)
    {
        $user = auth()->user();
        
        if ($user) {
            // Log logout
            $this->logActivity(
                'logout',
                'auth',
                "User logged out: {$user->name} ({$user->email})",
                null,
                ['user_id' => $user->id],
                null,
                'success'
            );
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'You have been logged out successfully.');
    }


    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }


    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->logActivity(
                'password_reset_requested',
                'auth',
                "Password reset requested for email: {$request->email}",
                null,
                null,
                ['email' => $request->email],
                'success'
            );
            
            return back()->with(['success' => __($status)]);
        }

        $this->logActivity(
            'password_reset_failed',
            'auth',
            "Failed password reset request for email: {$request->email}",
            null,
            null,
            ['email' => $request->email, 'error' => __($status)],
            'failed'
        );

        return back()->withErrors(['email' => __($status)]);
    }


    public function showResetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }


    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
                
                // Log password reset
                $this->logActivity(
                    'password_reset',
                    'auth',
                    "Password reset for user: {$user->name} ({$user->email})",
                    null,
                    ['user_id' => $user->id],
                    null,
                    'success'
                );
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}