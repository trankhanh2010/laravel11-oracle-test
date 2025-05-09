<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token_header = $request->bearerToken();
        if (!$token_header) {
            return response()->json(['mess' => 'Thiếu token'], 401);
        }
        $token = get_token_header($request, $token_header);
        $user = get_user_with_loginname($token->login_name);
        // Nếu có full quyền thì đi tiếp

        $cacheKey = 'check_super_admin_'.$user->loginname;
        $cacheKeySet = "cache_keys:" . $user->loginname; // Set để lưu danh sách key
        $cacheKeySetS = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $check_super_admin =  Cache::remember($cacheKey, now()->addMinutes(1440) , function () use ($user) {
            return $user->checkSuperAdmin();
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
        Redis::connection('cache')->sadd($cacheKeySetS, [$cacheKey]);

        if($check_super_admin){
            return $next($request);
        }
        return return403();
    }
}
