<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{

    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'), 
            'status' => session('status'),
        ]);
    }


    public function store(LoginRequest $request): RedirectResponse
    {
        // Kiểm tra email và password không được bỏ trống
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Vui lòng nhập đầy đủ thông tin.',
            'password.required' => 'Vui lòng nhập đầy đủ thông tin.',
        ]);

        $request->authenticate();

        $user = Auth::user();

        // Kiểm tra tài khoản bị khóa (status = 0)
        if ($user->status === 0) {
            // Đăng xuất ngay lập tức
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Ném lỗi với thông báo tài khoản bị khóa
            throw ValidationException::withMessages([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        $request->session()->regenerate();

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }


    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate(); 

        $request->session()->regenerateToken();

        return redirect('/');
    }
}