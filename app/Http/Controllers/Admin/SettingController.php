<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {

        $settings = [];
        

        if (Schema::hasTable('settings')) {
            $settings = DB::table('settings')->pluck('value', 'key')->toArray();
        }
        

        $defaults = [
            'store_name' => 'BigBag.vn',
            'store_email' => 'contact@bigbag.vn',
            'store_phone' => '1900 1234',
            'store_address' => '123 Đường ABC, Quận 1, TP.HCM',
            'tax_code' => '',
            'b2b_email' => 'b2b@bigbag.vn',
            'preorder_deposit' => 30,
            'preorder_lead_time' => 15,
            'payment_cod' => true,
            'payment_bank' => true,
            'payment_momo' => false,
            'payment_vnpay' => false,
            'seo_title' => 'BigBag.vn - Balo và Túi xách cao cấp',
            'seo_description' => 'BigBag.vn chuyên cung cấp balo, túi xách cao cấp',
            'seo_keywords' => 'balo, túi xách, phụ kiện'
        ];
        
        $merged = array_merge($defaults, $settings);
        

        $users = User::select('id', 'name', 'email', 'role', 'status')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'permission' => $user->role === 'admin' ? 'Full' : 'Chỉ đơn hàng'
                ];
            });
        
        return Inertia::render('Admin/Settings', [
            'settings' => $merged,
            'users' => $users
        ]);
    }
    
    public function updateGeneral(Request $request)
    {
        try {
            $data = $request->validate([
                'store_name' => 'required|string|max:255',
                'store_email' => 'required|email',
                'store_phone' => 'required|string|max:20',
                'store_address' => 'nullable|string',
                'tax_code' => 'nullable|string|max:50',
                'b2b_email' => 'nullable|email',
                'preorder_deposit' => 'required|integer|min:0|max:100',
                'preorder_lead_time' => 'required|integer|min:1|max:365',
                'payments' => 'nullable|array',
                'seo' => 'nullable|array'
            ]);
            

            if (!Schema::hasTable('settings')) {

                session([
                    'store_name' => $data['store_name'],
                    'store_email' => $data['store_email'],
                    'store_phone' => $data['store_phone'],
                    'store_address' => $data['store_address'] ?? '',
                    'tax_code' => $data['tax_code'] ?? '',
                    'b2b_email' => $data['b2b_email'] ?? '',
                    'preorder_deposit' => $data['preorder_deposit'],
                    'preorder_lead_time' => $data['preorder_lead_time'],
                    'payment_cod' => $data['payments']['cod'] ?? false,
                    'payment_bank' => $data['payments']['bank'] ?? false,
                    'payment_momo' => $data['payments']['momo'] ?? false,
                    'payment_vnpay' => $data['payments']['vnpay'] ?? false,
                    'seo_title' => $data['seo']['title'] ?? '',
                    'seo_description' => $data['seo']['description'] ?? '',
                    'seo_keywords' => $data['seo']['keywords'] ?? ''
                ]);
            } else {

                $this->saveSetting('store_name', $data['store_name']);
                $this->saveSetting('store_email', $data['store_email']);
                $this->saveSetting('store_phone', $data['store_phone']);
                $this->saveSetting('store_address', $data['store_address'] ?? '');
                $this->saveSetting('tax_code', $data['tax_code'] ?? '');
                $this->saveSetting('b2b_email', $data['b2b_email'] ?? '');
                $this->saveSetting('preorder_deposit', $data['preorder_deposit']);
                $this->saveSetting('preorder_lead_time', $data['preorder_lead_time']);
                $this->saveSetting('payment_cod', $data['payments']['cod'] ?? false);
                $this->saveSetting('payment_bank', $data['payments']['bank'] ?? false);
                $this->saveSetting('payment_momo', $data['payments']['momo'] ?? false);
                $this->saveSetting('payment_vnpay', $data['payments']['vnpay'] ?? false);
                $this->saveSetting('seo_title', $data['seo']['title'] ?? '');
                $this->saveSetting('seo_description', $data['seo']['description'] ?? '');
                $this->saveSetting('seo_keywords', $data['seo']['keywords'] ?? '');
            }
            
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công!']);
        } catch (\Exception $e) {
            Log::error('Lỗi update settings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function saveSetting($key, $value)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => is_bool($value) ? ($value ? 'true' : 'false') : (string)$value, 'updated_at' => now()]
        );
    }
    
    public function changePassword(Request $request)
    {
        try {
            $user = auth()->user();
            
            \Log::info('Change password attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'current_password_input' => $request->current_password
            ]);
            
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);
            

            if (!Hash::check($request->current_password, $user->password)) {
                \Log::warning('Current password mismatch', [
                    'user_id' => $user->id,
                    'hashed_password_in_db' => $user->password
                ]);
                return response()->json(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng!'], 400);
            }
            

            $user->password = Hash::make($request->new_password);
            $user->save();
            
            \Log::info('Password changed successfully', ['user_id' => $user->id]);
            
            return response()->json(['success' => true, 'message' => 'Đổi mật khẩu thành công!']);
            
        } catch (\Exception $e) {
            \Log::error('Change password error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function getUsers()
    {
        $users = User::select('id', 'name', 'email', 'role', 'status')
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($users);
    }
    
    public function storeUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role' => 'required|in:admin, user',
                'status' => 'boolean'
            ]);
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->status ?? 1
            ]);
            
            return response()->json(['success' => true, 'message' => 'Thêm thành công!', 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function updateUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'role' => 'required|in:admin,user',
                'status' => 'boolean'
            ]);
            
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'status' => $request->status ?? $user->status
            ]);
            
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công!', 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function destroyUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->id === auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Không thể xóa chính mình!'], 400);
            }
            
            $user->delete();
            return response()->json(['success' => true, 'message' => 'Xóa thành công!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function toggleUserStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->id === auth()->id()) {
                return response()->json(['success' => false, 'message' => 'Không thể thay đổi trạng thái chính mình!'], 400);
            }
            
            $user->status = !$user->status;
            $user->save();
            
            return response()->json(['success' => true, 'message' => $user->status ? 'Đã kích hoạt!' : 'Đã khóa!', 'data' => $user]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    
}