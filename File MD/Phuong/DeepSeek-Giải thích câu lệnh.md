# Password defaults location

**Created:** 06/16/2026 12:00  
**Updated:** 06/16/2026 16:02  
**Exported:** 06/21/2026 00:47  
**Link:** [https://chat.deepseek.com/a/chat/s/6598840a-69c3-4a34-bfac-b8e92b7baed4](https://chat.deepseek.com/a/chat/s/6598840a-69c3-4a34-bfac-b8e92b7baed4)  

## Prompt:
06/16/2026 12:00

Rules\Password::defaults()
Trong laravel thì cái này nằm ở đâu?


## Response:
06/16/2026 12:00

> **Thinking:**
> Người dùng hỏi về vị trí của `Rules\Password::defaults()` trong Laravel. Đây là một câu hỏi về framework. Tôi cần tìm kiếm thông tin về phương thức này. Có thể nó nằm trong một số phiên bản Laravel. Tôi sẽ tìm kiếm.Kết quả tìm kiếm cho thấy có một số bài viết. Để trả lời câu hỏi của người dùng, tôi cần xác định vị trí của `Rules\Password::defaults()`. Phương thức này nằm trong lớp `Illuminate\Validation\Rules\Password`. Người dùng có thể muốn biết vị trí tệp tin. Tôi sẽ mở một số kết quả để có thêm thông tin.Câu trả lời sẽ đề cập đến vị trí của phương thức `defaults()` trong lớp `Illuminate\Validation\Rules\Password`. Tôi sẽ giải thích rằng nó được sử dụng để xác định các quy tắc mật khẩu mặc định và thường được gọi trong một service provider. Tôi cũng sẽ đề cập đến các yêu cầu về phiên bản. Tôi sẽ trích dẫn các nguồn phù hợp.

Phương thức `Rules\Password::defaults()` (hay đầy đủ là `Illuminate\Validation\Rules\Password::defaults()`) nằm trong **Lớp `Password` của Laravel**, thuộc không gian tên `Illuminate\Validation\Rules`.

Bạn có thể tìm thấy định nghĩa của nó trong mã nguồn của Laravel Framework tại đường dẫn:
`vendor/laravel/framework/src/Illuminate/Validation/Rules/Password.php` [0](https://stackoverflow.com/questions/67875161/does-defaults-method-exist-in-password/67875319#67875319)

### Vị trí sử dụng trong ứng dụng
Phương thức này thường được dùng để **thiết lập các quy tắc xác thực mật khẩu mặc định** cho toàn bộ ứng dụng. Bạn sẽ đặt nó trong phương thức `boot()` của một **Service Provider**, thường là `App\Providers\AppServiceProvider` [2](https://laravel-news.com/defining-default-password-validation-rules?utm_campaign=Laravel%20913%20Bouncer%20v1%20Tinkerwell%20v3%20and%20Examples%20of%20N1%20Query%20Problems%20-%20410&utm_content=Laravel%20913%20Bouncer%20v1%20Tinkerwell%20v3%20and%20Examples%20of%20N1%20Query%20Problems%20-%20410+CID_be121e9184a7e6b6f1c9cd0a1a0b30aa&utm_medium=email&utm_source=email%20marketing&utm_term=Defining%20Default%20Password%20Validation%20Rules%20in%20Laravel).

Ví dụ:
```php
use Illuminate\Validation\Rules\Password;

public function boot()
{
    Password::defaults(function () {
        return Password::min(8)
            ->mixedCase()
            ->uncompromised();
    });
}
```
Sau đó, bạn có thể sử dụng các quy tắc mặc định này trong validator với `Password::defaults()` [4](https://laravel-news.com/defining-default-password-validation-rules?utm_campaign=Laravel%20913%20Bouncer%20v1%20Tinkerwell%20v3%20and%20Examples%20of%20N1%20Query%20Problems%20-%20410&utm_content=Laravel%20913%20Bouncer%20v1%20Tinkerwell%20v3%20and%20Examples%20of%20N1%20Query%20Problems%20-%20410+CID_be121e9184a7e6b6f1c9cd0a1a0b30aa&utm_medium=email&utm_source=email%20marketing&utm_term=Defining%20Default%20Password%20Validation%20Rules%20in%20Laravel).

### Lưu ý về phiên bản Laravel
Tính năng này được giới thiệu từ **Laravel 8.42.0** (phát hành ngày 18 tháng 5 năm 2021) [6](https://stackoverflow.com/questions/67875161/does-defaults-method-exist-in-password/67875319#67875319). Hãy đảm bảo dự án của bạn đang sử dụng phiên bản tương thích.

> **Defining Default Password Validation Rules in Laravel - Laravel News** [Laravel News](https://laravel-news.com/defining-default-password-validation-rules?utm_campaign=Laravel%20913%20Bouncer%20v1%20Tinkerwell%20v3%20and%20Examples%20of%20N1%20Query%20Problems%20-%20410&utm_content=Laravel%20913%20Bouncer%20v1%20Tinkerwell%20v3%20and%20Examples%20of%20N1%20Query%20Problems%20-%20410+CID_be121e9184a7e6b6f1c9cd0a1a0b30aa&utm_medium=email&utm_source=email%20marketing&utm_term=Defining%20Default%20Password%20Validation%20Rules%20in%20Laravel)
>
> **Does defaults() method exist in Password?** [Stack Overflow](https://stackoverflow.com/questions/67875161/does-defaults-method-exist-in-password/67875319#67875319)
>


## Prompt:
06/16/2026 12:01

Các hàm của thư viện này là gì?


## Response:
06/16/2026 12:01

> **Thinking:**
> The user wants to know the functions of the Password rule library in Laravel. I need to provide a comprehensive list of the methods available in the `Illuminate\Validation\Rules\Password` class. I'll search for the official documentation or a reliable source.The search results include some relevant pages. To get a comprehensive list of methods, I should open the official documentation or a reliable source like Laravel News or the API documentation. I'll open result 5 from the first search, which seems to be the API documentation for the Password class. I'll also open result 0 from the third search, which might list methods. Additionally, I'll open result 6 from the first search, which is a Laravel News article about the Password rule object.The API documentation page lists many methods. I'll scroll further to see all of them.I also see methods like `when`, `unless`, `defaults`, `default`, `required`, `sometimes`, `setValidator`, `setData`, `min`, `max`, `uncompromised`, `mixedCase`, `letters`, `numbers`, `symbols`, `rules`, `passes`, `message`, `fail`, `appliedRules`, `getIterator`. I should also mention `toPasswordRulesString` which was introduced in Laravel 13.9.0. I'll organize these into categories: static methods, fluent constraints, utility methods, etc. I'll provide a clear list with descriptions.

Lớp `Illuminate\Validation\Rules\Password` cung cấp các hàm (method) sau, được chia thành các nhóm chức năng:

---

### 1. Khởi tạo & Cấu hình mặc định (Static Methods)

| Phương thức | Mô tả |
|-------------|-------|
| `Password::min(int $size)` | Tạo instance mới với độ dài tối thiểu |
| `Password::defaults(callable\|null $callback)` | Thiết lập callback mặc định cho toàn bộ ứng dụng [1](https://api.laravel.com/docs/master/Illuminate/Validation/Rules/Password.html) |
| `Password::default()` | Lấy cấu hình mặc định của rule password [2](https://api.laravel.com/docs/master/Illuminate/Validation/Rules/Password.html) |
| `Password::required()` | Lấy cấu hình mặc định và đánh dấu trường là bắt buộc [3](https://api.laravel.com/docs/master/Illuminate/Validation/Rules/Password.html) |
| `Password::sometimes()` | Lấy cấu hình mặc định và đánh dấu trường là "sometimes" |

---

### 2. Ràng buộc độ phức tạp (Fluent Methods - Chainable)

| Phương thức | Mô tả |
|-------------|-------|
| `min(int $size)` | Đặt độ dài tối thiểu |
| `max(int $size)` | Đặt độ dài tối đa |
| `mixedCase()` | Yêu cầu ít nhất 1 chữ hoa và 1 chữ thường |
| `letters()` | Yêu cầu ít nhất 1 chữ cái (a-z hoặc A-Z) |
| `numbers()` | Yêu cầu ít nhất 1 chữ số |
| `symbols()` | Yêu cầu ít nhất 1 ký tự đặc biệt |
| `uncompromised(int $threshold = 0)` | Kiểm tra mật khẩu có bị lộ trong các vụ rò rỉ dữ liệu hay không (dùng API Have I Been Pwned). Tham số `$threshold` là số lần xuất hiện tối đa cho phép trong các vụ rò rỉ [12](https://laravel-news.com/password-validation-rule-object-in-laravel-8?utm_campaign=Top%2010%20Laravel%20Audit%20Security%20Issues%20Laravel%20109%20and%20more%20-%20459&utm_content=Top%2010%20Laravel%20Audit%20Security%20Issues%20Laravel%20109%20and%20more%20-%20459+CID_223e7c17badbfcde91e40947ec7d4832&utm_medium=email&utm_source=email%20marketing&utm_term=Password%20Validation%20Rule%20Object%20in%20Laravel%208) |
| `rules(Closure\|string\|array $rules)` | Thêm các rule validation bổ sung |

---

### 3. Tiện ích & Hỗ trợ (Utility Methods)

| Phương thức | Mô tả |
|-------------|-------|
| `appliedRules()` | Trả về mảng associative chứa tất cả rule đang active và giá trị của chúng |
| `toPasswordRulesString()` | (**Laravel 13.9.0+**) Chuyển instance Password thành chuỗi attribute `passwordrules` cho HTML, giúp trình duyệt và password manager tự động tạo mật khẩu hợp lệ [16](https://laravel-news.com/laravel-13-9-0?utm_medium=feed&utm_source=feedpress.me&utm_campaign=Feed%3A+laravelnews), [17](https://laravel-news.com/laravel-13-9-0?utm_medium=feed&utm_source=feedpress.me&utm_campaign=Feed%3A+laravelnews) |
| `passes(string $attribute, mixed $value)` | Xác định rule validation có pass hay không |
| `message()` | Lấy thông báo lỗi validation |
| `fail(array\|string $messages)` | Thêm lỗi và trả về `false` |
| `getIterator()` | Lấy iterator cho các rule password |
| `when($value, callable $callback, callable $default = null)` | Áp dụng callback nếu giá trị là truthy [22](https://api.laravel.com/docs/master/Illuminate/Validation/Rules/Password.html) |
| `unless($value, callable $callback, callable $default = null)` | Áp dụng callback nếu giá trị là falsy [23](https://api.laravel.com/docs/master/Illuminate/Validation/Rules/Password.html) |
| `setValidator(Validator $validator)` | Set validator đang thực thi |
| `setData(array $data)` | Set dữ liệu đang được validate |

---

### Ví dụ sử dụng kết hợp

```php
use Illuminate\Validation\Rules\Password;

// Định nghĩa rule mặc định trong AppServiceProvider
Password::defaults(fn () => Password::min(12)
    ->max(64)
    ->mixedCase()
    ->numbers()
    ->symbols()
    ->uncompromised()
);

// Sử dụng trong validation
$request->validate([
    'password' => ['required', 'confirmed', Password::default()]
]);

// Lấy danh sách rule đang active
Password::default()->appliedRules();

// Tạo HTML passwordrules attribute (Laravel 13.9+)
Password::defaults()->toPasswordRulesString();
// Output: 'minlength: 12; maxlength: 64; required: lower; required: upper; required: digit; required: special;'
``` [26](https://laravel-news.com/laravel-13-9-0?utm_medium=feed&utm_source=feedpress.me&utm_campaign=Feed%3A+laravelnews), [27](https://laravel-news.com/laravel-13-9-0?utm_medium=feed&utm_source=feedpress.me&utm_campaign=Feed%3A+laravelnews)

> **Password Validation Rule Object in Laravel 8 - Laravel News** [Laravel News](https://laravel-news.com/password-validation-rule-object-in-laravel-8?utm_campaign=Top%2010%20Laravel%20Audit%20Security%20Issues%20Laravel%20109%20and%20more%20-%20459&utm_content=Top%2010%20Laravel%20Audit%20Security%20Issues%20Laravel%20109%20and%20more%20-%20459+CID_223e7c17badbfcde91e40947ec7d4832&utm_medium=email&utm_source=email%20marketing&utm_term=Password%20Validation%20Rule%20Object%20in%20Laravel%208)
>
> **Password** [Laravel](https://api.laravel.com/docs/master/Illuminate/Validation/Rules/Password.html)
>
> **Generate HTML Password Rules Attribute in Laravel 13.9.0 - Laravel News** [Laravel News](https://laravel-news.com/laravel-13-9-0?utm_medium=feed&utm_source=feedpress.me&utm_campaign=Feed%3A+laravelnews)
>


## Prompt:
06/16/2026 12:04

uncompromised() là gì?



---
Powered by [DeepSeek Chat Exporter](https://deepseekchatexporter.com)
