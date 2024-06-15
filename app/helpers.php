<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use App\Models\ACS\Token;
use App\Models\ACS\User;
use App\Models\HIS\UserRoom;
use Illuminate\Support\Facades\Request;

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

if (!function_exists('get_cache_full')) {
    function get_cache_full($model, $relation_ship, $name, $id = null, $time)
    {
        if (!$id) {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship) {
                return $model::with($relation_ship)->get();
            });
            return $data;
        } else {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id) {
                return $model::where('id', $id)->with($relation_ship)->get();
            });
            return $data;
        }
    }
}

if (!function_exists('get_cache_full_select')) {
    function get_cache_full_select($model, $relation_ship, $select, $name, $id = null, $time)
    {
        if (!$id) {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $select) {
                return $model::select($select)->with($relation_ship)->get();
            });
            return $data;
        } else {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id, $select) {
                return $model::select($select)->where('id', $id)->with($relation_ship)->get();
            });
            return $data;
        }
    }
}

if (!function_exists('get_cache_full_select_paginate')) {
    function get_cache_full_select_paginate($model, $relation_ship, $per_page, $select, $name, $id = null, $time)
    {
        if (!$id) {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $select, $per_page) {
                return $model->with($relation_ship)->paginate($per_page);
            });
            return $data;
        } else {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id, $select) {
                return $model::select($select)->where('id', $id)->with($relation_ship)->get();
            });
            return $data;
        }
    }
}

if (!function_exists('update_cache')) {
    function update_cache($name, $data_update, $time)
    {
        $data = Cache::remember($name, $time, function () use ($data_update) {
            return $data_update;
        });
        return $data;
    }
}

// if (!function_exists('cache_model_construct')) {
//     function cache_model_construct($name, $model, $time)
//     {
//         $data = Cache::remember($name.'_construct', $time, function () use ($model) {
//             return $model::lazy()->all();
//         });
//         return $data;
//     }
// }

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
    function get_cache_by_code($model, $name, $param, $type_name, $type, $time)
    {
        if ($type == null) {
            $data = Cache::remember($name . '_by_' . $type_name . '_' . $type, $time, function () use ($model, $param, $type_name, $type) {
                return $model::with($param)->get();
            });
        } else {
            $data = Cache::remember($name . '_by_' . $type_name . '_' . $type, $time, function () use ($model, $param, $type_name, $type) {
                return $model::with($param)->where($type_name, 'LIKE', $type . '%')->get();
            });
        }
        return $data;
    }
}

if (!function_exists('view_service_req')) {
    function view_service_req($execute_room_id, $token, $time)
    {
        $loginname = Cache::remember('token_' . $token . '_loginname', $time, function () use ($token) {
            return Token::select()->where('token_code', '=', $token)->value('login_name');
        });
        $user = get_user_with_loginname($loginname);
        if($user->checkSuperAdmin()){
            return true;
        }
        $check = Cache::remember('loginname_check_execute_room_id_' . $execute_room_id, $time, function () use ($loginname, $execute_room_id, $time) {
            $user_room = new UserRoom();
            return UserRoom::with('room.execute_room')
                ->whereHas('room.execute_room', function ($query) use ($execute_room_id) {
                    $query->where('id', $execute_room_id);
                })->exists();
        });
    }
}


if (!function_exists('get_loginname_with_token')) {
    function get_loginname_with_token($token, $time)
    {
        $loginname = Cache::remember('token_' . $token . '_loginname', $time, function () use ($token) {
            return Token::select()->where('token_code', '=', $token)->value('login_name');
        });
        return $loginname;
    }
}
