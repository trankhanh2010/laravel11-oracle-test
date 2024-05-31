<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Module;
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
        $currentRouteName = Route::currentRouteName();
        $token_header = $request->bearerToken();
        if (!$token_header) {
            return response()->json(['mess' => 'Thiếu token'], 401);
        }
        $token = get_token_header($request, $token_header);
        $user = get_user_with_loginname($token->login_name);
        // Nếu module vô danh thì đi tiếp
        $is_anonymous =  Cache::remember('is_anonymous_'.$currentRouteName, $time , function () use ($currentRouteName) {
            return Module::select('is_anonymous')->where('module_link',$currentRouteName)->value('is_anonymous');
        });
        if($is_anonymous){
            return $next($request);
        }
        // Nếu có full quyền thì đi tiếp
        if($user->checkSuperAdmin()){
            return $next($request);
        }
        // Kiểm tra quyền module
        if ($user->hasModule($currentRouteName)) {
            return $next($request);
        }
        return response()->json(['mess' => '403'], 403);
    }
}
