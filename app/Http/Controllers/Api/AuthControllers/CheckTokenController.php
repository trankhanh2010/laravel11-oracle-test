<?php

namespace App\Http\Controllers\Api\AuthControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\Controller;
use App\Models\ACS\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckTokenController extends Controller
{
    public function index(Request $request)
    {
        // Lấy Bearer token từ request
        $bearerToken = $request->bearerToken();
    
        // Kiểm tra nếu không có token
        if (!$bearerToken) {
            return response()->json(['success' => false], 400);
        }
    
        // Kiểm tra xem cache có tồn tại không
        $cacheKey = 'token_' . $bearerToken;
        if (Cache::has($cacheKey)) {
            return response()->json(['success' => true], 200);
        }
    
        return response()->json(['success' => false], 404);
    }
    public function logOut(Request $request){
        // Lấy Bearer token từ request
        $bearerToken = $request->bearerToken();
        // Kiểm tra nếu không có token
        if (!$bearerToken) {
            return return401();
        }
        $user = Token::where('token_code', $bearerToken)->firstOrFail();
        if($user){
            // Xóa cache liên quan đến token
            $cacheKey = 'token_' . $bearerToken;
            $cacheToken = Cache::get($cacheKey);
            if($cacheToken){
                $deleteToken = Cache::forget($cacheKey);
                // Xóa hết mọi cache liên quan đến user này
                event(new DeleteCache($user->login_name));
                if($deleteToken){
                    return $this->deleteTokenDB($bearerToken);
                }else{
                    return response()->json(['success' => false, 'message' => 'Đăng xuất thất bại! Lỗi khi xóa cache!'], 200);
                }
            }else{
                return $this->deleteTokenDB($bearerToken);
            }
        }
        return response()->json(['success' => false, 'message' => 'Đăng xuất thất bại! Người dùng k tồn tại!'], 200);
    }
    public function deleteTokenDB($bearerToken){
        $deleteToken = true;
        // $deleteToken = Token::where('token_code', $bearerToken)->delete();
        if($deleteToken){
            return response()->json(['success' => true, 'message' => 'Đăng xuất thành công!'], 200);
        }else{
            return response()->json(['success' => false, 'message' => 'Đăng xuất thất bại! Lỗi khi xóa token'], 200);
        }
    }
}
