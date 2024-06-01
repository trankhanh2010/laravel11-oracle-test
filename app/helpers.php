<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use App\Models\Token;
use App\Models\User;


if (!function_exists('get_user_with_loginname')) {
    function get_user_with_loginname($loginname)
    {
        $user = Cache::remember('user_' . $loginname, now()->addMinutes(1440), function () use ($loginname) {
            return User::select()->where("loginname", $loginname)->first();
        });
        return $user;
    }
}

if (!function_exists('get_token_header')) {
    function get_token_header($request, $token_header)
    {
        $token = Cache::remember('token_' . $token_header, now()->addMinutes(1440), function () use ($token_header) {
            return Token::where("token_code", $token_header)->first();
        });
        return $token;
    }
}

if (!function_exists('get_cache')) {
    function get_cache($model, $name, $id = null, $time)
    {
        if (!$id) {
            $data = Cache::remember($name, $time, function () use ($model) {
                return $model->all();
            });
            return $data;
        } else {
            if (!is_numeric($id)) {
                return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
            }
            $data = Cache::remember($name . '_id_' . $id, $time, function () use ($model, $id) {
                return $model->find($id);
            });
            // Xóa Cache nếu không có dữ liệu
            if (!$data) Cache::forget($name . '_id_' . $id);
            return $data;
        }
    }
}


if (!function_exists('get_cache_1_1')) {
    function get_cache_1_1($model, $relationship_name, $name, $id = null, $time)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
        }
        $data = Cache::remember($name . '_get_' . $relationship_name . '_' . $id, $time, function () use ($model, $relationship_name, $id) {
            return $model->find($id)->$relationship_name()->get();
        });
        return $data;
    }
}

if (!function_exists('get_cache_1_n')) {
    function get_cache_1_n($model, $relationship_name, $name, $id = null, $time)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
        }
        $data = Cache::remember($name . '_get_' . $relationship_name . '_' . $id, $time, function () use ($model, $relationship_name, $id) {
            $relationship_name = $relationship_name . 's';
            return $model->find($id)->$relationship_name()->get();
        });
        return $data;
    }
}

if (!function_exists('get_cache_1_n_with_ids')) {
    function get_cache_1_n_with_ids($model, $relationship_name, $name, $id = null, $time)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
        }
        $data = Cache::remember($name . '_get_' . $relationship_name . 's_' . $id, $time, function () use ($model, $relationship_name, $id) {
            $relationship_name = $relationship_name . 's';
            return $model->find($id)->$relationship_name();
        });
        return $data;
    }
}

if (!function_exists('get_cache_1_1_n_with_ids')) {
    function get_cache_1_1_n_with_ids($model, $relationship_name, $name, $id = null, $time)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
        }
        $data = Cache::remember($name . '_get_' . $relationship_name . '_' . $id, $time, function () use ($model, $relationship_name, $id) {
            $parts = explode(".", $relationship_name);
            $a0 = $parts[0];
            $a1 = $parts[1] . 's';
            return $model::with($a0)->find($id)->$a0->$a1();
        });
        return $data;
    }
}

if (!function_exists('get_cache_1_1_1')) {
    function get_cache_1_1_1($model, $relationship_name, $name, $id = null, $time)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
        }
        $data = Cache::remember($name . '_get_' . $relationship_name . '_' . $id, $time, function () use ($model, $relationship_name, $id) {
            $parts = explode(".", $relationship_name);
            $a0 = $parts[0];
            $a1 = $parts[1];
            return $model::with($relationship_name)->find($id)->$a0->$a1;
        });
        return $data;
    }
}


if (!function_exists('get_cache_1_1_1_1')) {
    function get_cache_1_1_1_1($model, $relationship_name, $name, $id = null, $time)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
        }
        $data = Cache::remember($name . '_get_' . $relationship_name . '_' . $id, $time, function () use ($model, $relationship_name, $id) {
            $parts = explode(".", $relationship_name);
            $a0 = $parts[0];
            $a1 = $parts[1];
            $a2 = $parts[2];
            return $model::with($relationship_name)->find($id)->$a0->$a1->$a2;
        });
        return $data;
    }
}

if (!function_exists('get_cache_1_1_1_1_1')) {
    function get_cache_1_1_1_1_1($model, $relationship_name, $name, $id = null, $time)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'Id không hợp lệ'], 400)->original;
        }
        $data = Cache::remember($name . '_get_' . $relationship_name . '_' . $id, $time, function () use ($model, $relationship_name, $id) {
            $parts = explode(".", $relationship_name);
            $a0 = $parts[0];
            $a1 = $parts[1];
            $a2 = $parts[2];
            $a3 = $parts[3];
            return $model::with($relationship_name)->find($id)->$a0->$a1->$a2->$a3;
        });
        return $data;
    }
}

if (!function_exists('get_cache_by_code')) {
    function get_cache_by_code($model, $name, $type_name, $type, $time)
    {
        $data = Cache::remember($name . '_by_' . $type_name , $time, function () use ($model, $type_name, $type) {
            return $model::where($type_name, 'LIKE', $type . '%')->get();
        });
        return $data;
    }
}