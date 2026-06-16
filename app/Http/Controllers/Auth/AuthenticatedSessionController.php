<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'), //Nếu có route password.request thì mới hiển thị link quên mật khẩu
            'status' => session('status'), //Lấy giá trị của session có tên là status, 
            // thường dùng để hiển thị thông báo sau khi thực hiện một hành động nào đó, 
            // VD: sau khi đăng xuất thành công, sẽ có session('status') = 'Đăng xuất thành công', rồi hiển thị thông báo này trên trang login.
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); //Xác thực thông tin đăng nhập của người dùng dựa trên các quy tắc đã được định nghĩa trong lớp LoginRequest.

        $request->session()->regenerate(); //Tạo session mới

        // Lấy thông tin user vừa đăng nhập
        $user = Auth::user();

        // Nếu user có role = 'admin' thì chuyển hướng đến admin dashboard
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Mặc định cho user thường (không phải admin)
        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate(); //Hủy session

        $request->session()->regenerateToken(); //Tạo token mới để ngăn chặn tấn công CSRF

        return redirect('/');
    }
}