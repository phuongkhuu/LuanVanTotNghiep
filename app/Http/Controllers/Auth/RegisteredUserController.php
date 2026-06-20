<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register'); //Inertia sẽ tìm đến resources/js/Pages/ + đường dẫn
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'phone' => 'required|string|max:20|unique:'.User::class, // 🔥 Thêm validation cho phone
            'password' => ['required', 'confirmed', Rules\Password::defaults()], 
            //Dựa theo ràng buộc của class rules/password. Chỉnh trong Provider/AppServiceProvider.php
        ]); 
        //Bắt buộc phải có name, email, phone, password. Email và phone phải là duy nhất trong bảng users.
        //Password phải được xác nhận (có trường password_confirmation) và tuân theo các quy tắc mặc định 
        // của Laravel.

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone, // 🔥 Lưu số điện thoại
            'password' => Hash::make($request->password), 
            //Hash bằng thuật toán bcrypt, tạo ra một chuỗi hash an toàn cho mật khẩu. 
            // Hash này sẽ được lưu vào cơ sở dữ liệu thay vì mật khẩu gốc, 
            // giúp bảo vệ thông tin đăng nhập của người dùng.
        ]);
        //Xài Eloquent để tạo một bản ghi mới trong bảng users với name, email, phone và password đã được hash.

        event(new Registered($user)); //Phát ra sự kiện Registered sau khi người dùng mới được tạo.
        //Để chạy bất kì chức năng nào được thực hiện khi 1 người dùng đăng ký
        //VD: Khi 1 người dùng vừa đăng ký, cần gửi email xác thực cho họ
        //VD: Khi 1 người dùng đăng ký, xét xem họ có phải user thứ 1000 hay không, nếu có thì tặng họ 100 điểm thưởng

        Auth::login($user); //Đăng nhập người dùng mới tạo vào hệ thống, thiết lập phiên làm việc.

        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}