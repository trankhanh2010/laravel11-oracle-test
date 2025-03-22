<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Models\ACS\User;
use App\Models\ACS\Module;
class CheckModule
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $time = now()->addMinutes(1440);
        $currentRouteName = substr(Route::currentRouteName(), 0, strrpos(Route::currentRouteName(), '->'));
        $token_header = $request->bearerToken();
        if (!$token_header) {
            return response()->json(['mess' => 'Thiếu token'], 401);
        }
        $token = get_token_header($request, $token_header);
        $user = get_user_with_loginname($token->login_name);
        // Nếu module vô danh thì đi tiếp
        $list_is_anonymous = Cache::remember('list_is_anonymous', now()->addMinutes(10080), function () {
            $data = Module::where('is_anonymous', 1)->pluck('module_link')->toArray();
            return base64_encode(gzcompress(serialize($data))); // Nén và mã hóa trước khi lưu
        });
        
        // Giải nén dữ liệu khi lấy từ cache
        if ($list_is_anonymous && is_string($list_is_anonymous)) {
            $decompressedData = @gzuncompress(base64_decode($list_is_anonymous));
            $list_is_anonymous = $decompressedData !== false ? unserialize($decompressedData) : [];
        }
        if(in_array($currentRouteName, $list_is_anonymous)){
            return $next($request);
        }
        // Nếu có full quyền thì đi tiếp
        $check_super_admin =  Cache::remember('check_super_admin_'.$user->loginname, now()->addMinutes(1440) , function () use ($user) {
            return $user->checkSuperAdmin();
        });
        if($check_super_admin){
            return $next($request);
        }
        // Kiểm tra quyền module
        $has_module =  Cache::remember('has_module_'.$currentRouteName.'_'.$user->loginname, now()->addMinutes(1440) , function () use ($user, $currentRouteName) {
            return $user->hasModule($currentRouteName);
        });
        if ($has_module) {
            return $next($request);
        }
        return return403();
    }
}
