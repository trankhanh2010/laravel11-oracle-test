<?php

namespace App\Http\Controllers\Api\AuthControllers;

use App\Http\Controllers\Controller;
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
}
