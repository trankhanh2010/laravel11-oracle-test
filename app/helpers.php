<?php

use App\Events\Telegram\SendMessageToChannel;
use App\Jobs\SendTelegramMessageJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use App\Models\ACS\Token;
use App\Models\ACS\User;
use App\Models\HIS\BhytParam;
use App\Models\HIS\Employee;
use App\Models\HIS\UserRoom;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;


if (!function_exists('tangTimeoutThemNGiay')) {
    function tangTimeoutThemNGiay($giay = 1)
    {
        $timeout = ini_get('max_execution_time'); // Lấy timeout hiện tại
        ini_set('max_execution_time', $timeout + $giay); // + n giây
    }
}
function create_slug($string)
{
    $search = array(
        '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
        '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
        '#(ì|í|ị|ỉ|ĩ)#',
        '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
        '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
        '#(ỳ|ý|ỵ|ỷ|ỹ)#',
        '#(đ)#',
        '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
        '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
        '#(Ì|Í|Ị|Ỉ|Ĩ)#',
        '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)#',
        '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
        '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
        '#(Đ)#',
        "/[^a-zA-Z0-9\-\_]/",
    );
    $replace = array(
        'a',
        'e',
        'i',
        'o',
        'u',
        'y',
        'd',
        'A',
        'E',
        'I',
        'O',
        'U',
        'Y',
        'D',
        '-',
    );
    $string = preg_replace($search, $replace, $string);
    $string = preg_replace('/(-)+/', ' ', $string);
    $string = strtolower($string);
    return $string;
}
function camelToSnake($input)
{
    $pattern = '/([a-z])([A-Z])/';
    $snake = strtolower(preg_replace($pattern, '$1_$2', $input));
    return $snake;
}

function convertKeysToSnakeCase(array $data)
{
    $result = [];
    foreach ($data as $key => $value) {
        $newKey = Str::snake($key);  // Chuyển đổi key sang snake_case

        // Nếu giá trị là một mảng, thực hiện đệ quy
        if (is_array($value)) {
            $result[$newKey] = convertKeysToSnakeCase($value);
        } else {
            $result[$newKey] = $value;
        }
    }
    return $result;
}
function snakeToCamel($string)
{
    // Chuyển chuỗi về dạng mảng với dấu gạch dưới làm phân tách
    $words = explode('_', $string);

    // Chuyển từ thứ hai trở đi thành chữ hoa đầu
    $words = array_map(function ($word, $index) {
        return $index == 0 ? $word : ucfirst($word);
    }, $words, array_keys($words));

    // Gộp mảng thành chuỗi
    return implode('', $words);
}
function camelCaseFromUnderscore($string)
{
    return lcfirst(preg_replace_callback('/(?:^|_)([a-z])/', function ($matches) {
        return strtoupper($matches[1]);
    }, $string));
}

function convertArrayKeysToSnakeCase(array $array)
{
    $result = [];
    foreach ($array as $key => $value) {
        $newKey = camelToSnake($key);
        $result[$newKey] = camelToSnake($value);
    }
    return $result;
}

function arrayToCustomString(array $array)
{
    $result = '';
    foreach ($array as $key => $value) {
        $result .= '_' . $key . '_' . $value;
    }
    return $result;
}

function arrayToCustomStringNotKey(array $array)
{
    $result = '';
    foreach ($array as $key => $value) {
        $result .= '_' . $value;
    }
    return $result;
}

if (!function_exists('get_user_with_loginname')) {
    function get_user_with_loginname($loginname)
    {
        $cacheKey = 'user_' . $loginname;
        $cacheKeySet = "cache_keys:" . $loginname; // Set để lưu danh sách key
        $cacheKeySetS = "cache_keys:" . "setting"; // Set để lưu danh sách key

        $user = Cache::remember($cacheKey, now()->addMinutes(1440), function () use ($loginname) {
            return User::select()->where("loginname", $loginname)->first();
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
        Redis::connection('cache')->sadd($cacheKeySetS, [$cacheKey]);

        return $user;
    }
}

if (!function_exists('get_token_header')) {
    function get_token_header($request, $token_header)
    {
        $record = Cache::get('token_' . $token_header);

        if (!$record) {
            $record = Token::where("token_code", $token_header)->first();

            if ($record && $record->expire_time) {
                // Chuyển đổi expire_time (YYYYMMDDHHMMSS) thành Carbon instance
                $expiresAt = \Carbon\Carbon::createFromFormat('YmdHis', $record->expire_time);

                // Tính toán thời gian còn lại
                $remainingTime = now()->diffInSeconds($expiresAt, false);
                if ($remainingTime > 0) {
                    // Lưu cache với thời gian hết hạn động
                    $cacheKey = 'token_' . $token_header;
                    $cacheKeySetS = "cache_keys:" . "setting"; // Set để lưu danh sách key
                    Cache::put($cacheKey, $record, $remainingTime);
                    Redis::connection('cache')->sadd($cacheKeySetS, [$cacheKey]);
                }
            }
        }
        return $record;
    }
}

if (!function_exists('get_cache')) {
    function get_cache($model, $name, $id, $time)
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
function get_cache($model, $name, $id, $time, $start, $limit, $order_by)
{
    if (!$id) {
        $data = Cache::remember($name, $time, function () use ($model, $start, $limit, $order_by) {
            $data = $model;
            $count = $data->count();
            if ($order_by != null) {
                foreach ($order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            $data = $data
                ->skip($start)
                ->take($limit)
                ->get();
            return ['data' => $data, 'count' => $count];
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


if (!function_exists('get_cache_full')) {
    // function get_cache_full($model, $relation_ship, $name, $id = null, $time)
    // {
    //     if (!$id) {
    //         $data = Cache::remember($name, $time, function () use ($model, $relation_ship) {
    //             return $model::with($relation_ship)->get();
    //         });
    //         return $data;
    //     } else {
    //         if($id == 'deleted'){
    //             $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id) {
    //                 return $model::withDeleted()->with($relation_ship)->get();
    //             });
    //         }
    //         else{
    //             $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id) {
    //                 return $model::where('id', $id)->with($relation_ship)->first();
    //             });
    //         }
    //         return $data;
    //     }
    // }
    function get_cache_full($model, $relation_ship, $name, $id, $time, $start, $limit, $order_by, $is_active, $get_all)
    {
        if (!$id) {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $start, $limit, $order_by, $is_active, $get_all) {
                $data = $model::with($relation_ship);
                if ($is_active !== null) {
                    $data = $data->where('is_active', $is_active);
                }
                $count = $data->count();
                if ($order_by != null) {
                    foreach ($order_by as $key => $item) {
                        $data->orderBy($key, $item);
                    }
                }
                if ($get_all) {
                    $data = $data
                        ->get();
                } else {
                    $data = $data
                        ->skip($start)
                        ->take($limit)
                        ->get();
                }
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } else {
            if ($id == 'deleted') {
                $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id) {
                    return $model::withDeleted()->with($relation_ship)->get();
                });
            } else {
                $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id, $is_active) {
                    if ($is_active !== null) {
                        return $model::where('id', $id)->where('is_active', $is_active)->with($relation_ship)->first();
                    } else {
                        return $model::where('id', $id)->with($relation_ship)->first();
                    }
                });
            }
            return $data;
        }
    }
}



if (!function_exists('get_cache_full_select')) {
    // function get_cache_full_select($model, $relation_ship, $select, $name, $id = null, $time)
    // {
    //     if (!$id) {
    //         $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $select) {
    //             return $model::select($select)->with($relation_ship)->get();
    //         });
    //         return $data;
    //     } else {
    //         $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id, $select) {
    //             return $model::select($select)->where('id', $id)->with($relation_ship)->get();
    //         });
    //         return $data;
    //     }
    // }
    function get_cache_full_select($model, $relation_ship, $select, $name, $id, $time, $start, $limit, $order_by, $is_active, $get_all)
    {
        if (!$id) {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $select, $start, $limit, $order_by, $is_active, $get_all) {
                $data =  $model::select($select)->with($relation_ship);
                if ($is_active !== null) {
                    $data = $data->where('is_active', $is_active);
                }
                $count = $data->count();
                if ($order_by != null) {
                    foreach ($order_by as $key => $item) {
                        $data->orderBy($key, $item);
                    }
                }
                if ($get_all) {
                    $data = $data
                        ->get();
                } else {
                    $data = $data
                        ->skip($start)
                        ->take($limit)
                        ->get();
                }
                return ['data' => $data, 'count' => $count];
            });
        } else {
            $data = Cache::remember($name, $time, function () use ($model, $relation_ship, $id, $select, $is_active) {
                if ($is_active !== null) {
                    return $model::select($select)->where('id', $id)->where('is_active', $is_active)->with($relation_ship)->get();
                } else {
                    return $model::select($select)->where('id', $id)->with($relation_ship)->get();
                }
            });
        }
        return $data;
    }
}

if (!function_exists('get_cache_full_select_paginate')) {
    function get_cache_full_select_paginate($model, $relation_ship, $per_page, $select, $name, $id, $time)
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
    function get_cache_1_1($model, $relationship_name, $name, $id, $time)
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
    function get_cache_1_n($model, $relationship_name, $name, $id, $time)
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
    function get_cache_1_n_with_ids($model, $relationship_name, $name, $id, $time)
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
    function get_cache_1_1_n_with_ids($model, $relationship_name, $name, $id, $time)
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
    function get_cache_1_1_1($model, $relationship_name, $name, $id, $time)
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
    function get_cache_1_1_1_1($model, $relationship_name, $name, $id, $time)
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
    function get_cache_1_1_1_1_1($model, $relationship_name, $name, $id, $time)
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
    // function get_cache_by_code($model, $name, $param, $type_name, $type, $time)
    // {
    //     if ($type == null) {
    //         $data = Cache::remember($name . '_by_' . $type_name . '_' . $type, $time, function () use ($model, $param, $type_name, $type) {
    //             return $model::with($param)->get();
    //         });
    //     } else {
    //         $data = Cache::remember($name . '_by_' . $type_name . '_' . $type, $time, function () use ($model, $param, $type_name, $type) {
    //             return $model::with($param)->where($type_name, 'LIKE', $type . '%')->get();
    //         });
    //     }
    //     return $data;
    // }
    function get_cache_by_code($model, $name, $param, $type_name, $type, $time, $start, $limit)
    {
        if ($type == null) {
            $data = Cache::remember($name . '_by_' . $type_name . '_' . $type . '_start_' . $start . '_limit_' . $limit, $time, function () use ($model, $param, $type_name, $type, $start, $limit) {
                $data =  $model->with($param);

                $count = $data->count();
                $data = $data
                    ->skip($start)
                    ->take($limit)
                    ->get();
                return ['data' => $data, 'count' => $count];
            });
        } else {
            $data = Cache::remember($name . '_by_' . $type_name . '_' . $type . '_start_' . $start . '_limit_' . $limit, $time, function () use ($model, $param, $type_name, $type, $start, $limit) {
                $data =  $model->with($param)
                    ->where($type_name, 'LIKE', $type . '%');
                $count = $data->count();
                $data = $data
                    ->skip($start)
                    ->take($limit)
                    ->get();
                return ['data' => $data, 'count' => $count];
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
        if ($user->checkSuperAdmin()) {
            return true;
        }
        $check = Cache::remember('loginname_check_execute_room_id_' . $execute_room_id, $time, function () use ($loginname, $execute_room_id, $time) {
            return UserRoom::with('room.execute_room')
                ->whereHas('room.execute_room', function ($query) use ($execute_room_id) {
                    $query->where('id', $execute_room_id);
                })->exists();
        });
        return $check;
    }
}


if (!function_exists('get_loginname_with_token')) {
    function get_loginname_with_token($token, $time = 14400)
    {
        $loginname = Cache::remember('token_' . $token . '_loginname', $time, function () use ($token) {
            return Token::select()->where('token_code', '=', $token)->value('login_name');
        });
        return $loginname;
    }
}
if (!function_exists('get_department_id_with_loginname')) {
    function get_department_id_with_loginname($loginname, $time = 14400)
    {
        $cacheKey = 'department_id_' . $loginname;
        $cacheKeySet = "cache_keys:" . 'setting'; // Set để lưu danh sách key

        $data = Cache::remember($cacheKey, $time, function () use ($loginname) {
            return Employee::where('loginname', $loginname)->value('department_id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
        return $data;
    }
}
if (!function_exists('get_username_with_token')) {
    function get_username_with_token($token, $time = 14400)
    {
        $username = Cache::remember('token_' . $token . '_username', $time, function () use ($token) {
            return Token::select()->where('token_code', '=', $token)->value('user_name');
        });
        return $username;
    }
}

if (!function_exists('returnIdError')) {
    function returnIdError($id)
    {
        return response()->json([
            'status'    => 422,
            'success'   => false,
            'message'   => 'Id = ' . $id . ' không hợp lệ!',
        ], 422);
    }
}

if (!function_exists('returnCodeError')) {
    function returnCodeError($code)
    {
        return response()->json([
            'status'    => 422,
            'success'   => false,
            'message'   => 'Code = ' . $code . ' không hợp lệ!',
        ], 422);
    }
}

if (!function_exists('returnNotRecord')) {
    function returnNotRecord($id)
    {
        return response()->json([
            'status'    => 422,
            'success'   => false,
            'message'   => 'Không tìm thấy bản ghi với id = ' . $id . '!',
        ], 422);
    }
}

if (!function_exists('returnDanhSachRong')) {
    function returnDanhSachRong()
    {
            return response()->json([
                'status'    => 200,
                'success' => true,
                'param' => [],
                'data' => [],
            ], 200);
    }
}

if (!function_exists('returnDataSuccess')) {
    function returnDataSuccess($param_return, $data_return)
    {
        if (is_array($data_return)) {
            $data_return = $data_return['data'] ?? $data_return;
        }
        if ($param_return == null) {
            return response()->json([
                'status'    => 200,
                'success' => true,
                'data' => $data_return,
            ], 200)->original;
        } else {
            return response()->json([
                'status'    => 200,
                'success' => true,
                'param' => $param_return,
                'data' => $data_return,
            ], 200);
        }
    }
}

if (!function_exists('returnDataCreateSuccess')) {
    function returnDataCreateSuccess($data_return)
    {
        return response()->json([
            'status'    => 201,
            'success' => true,
            'data' => $data_return,
        ], 201);
    }
}

if (!function_exists('returnDataUpdateSuccess')) {
    function returnDataUpdateSuccess($data_return)
    {
        return response()->json([
            'status'    => 200,
            'success' => true,
            'data' => $data_return,
        ], 200);
    }
}

if (!function_exists('returnDataDeleteSuccess')) {
    function returnDataDeleteSuccess()
    {
        return response()->json([
            'status'    => 200,
            'success' => true,
            'message' => 'Xóa bản ghi thành công!'
        ], 200);
    }
}

if (!function_exists('returnClearCache')) {
    function returnClearCache()
    {
        return response()->json([
            'status'    => 200,
            'success' => true,
            'message' => 'Thành công!'
        ], 200);
    }
}

if (!function_exists('return_data_delete_fail')) {
    function return_data_delete_fail()
    {
        return response()->json([
            'status'    => 400,
            'success' => false,
            'message' => 'Không thể xóa. Dữ liệu đã được sử dụng!'
        ], 400);
    }
}

if (!function_exists('return_data_fail_transaction')) {
    function return_data_fail_transaction()
    {
        return response()->json([
            'status'    => 500,
            'success' => false,
        ], 500);
    }
}

if (!function_exists('return403')) {
    function return403()
    {
        return response()->json([
            'status'    => 403,
            'success' => false,
            'message' => 'Không có quyền truy cập!'
        ], 403);
    }
}

if (!function_exists('return401')) {
    function return401()
    {
        return response()->json([
            'status'    => 401,
            'success' => false,
            'message' => 'Thiếu token!'
        ], 401);
    }
}

if (!function_exists('return400')) {
    function return400($mess)
    {
        // dd($mess);
        return response()->json([
            'status'    => 400,
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ!',
            'error' => $mess
        ], 400);
    }
}

if (!function_exists('returnCheckData')) {
    function returnCheckData($param, $available)
    {
        return response()->json([
            'status'    => 200,
            'success' => true,
            'param' => $param,
            'available' => $available,
        ], 200);
    }
}

if (!function_exists('returnParamError')) {
    function returnParamError()
    {
        return response()->json([
            'status'    => 422,
            'success' => false,
            'message' => 'param có dữ liệu không hợp lệ!'
        ], 422);
    }
}

if (!function_exists('return500Error')) {
    function return500Error($mess = null)
    {
        return response()->json([
            'status'    => 500,
            'success' => false,
            'message' => 'Có lỗi trong quá trình xử lý!',
            'detail' => $mess
        ], 500);
    }
}

if (!function_exists('return_404_error_page_not_found')) {
    function return_404_error_page_not_found()
    {
        return response()->json([
            'status'    => 404,
            'success' => false,
            'message' => 'Đường dẫn Api không hợp lệ!'
        ], 500);
    }
}

// Elastic Search

if (!function_exists('getArrElasticIndexKeyword')) {
    function getArrElasticIndexKeyword($name)
    {
        $time = 144000; // Thời gian lưu cache
        $data = Cache::remember('elastic_index_keyword_' . $name, $time, function () use ($name) {
            $keywordFields = [];
            $client = app('Elasticsearch');
            $params = [
                'index' => $name,
            ];
            $data = $client->indices()->getMapping($params)[$name];

            // Hàm đệ quy để tìm các trường keyword
            $findKeywordFields = function ($properties, $prefix = '') use (&$findKeywordFields, &$keywordFields) {
                foreach ($properties as $field => $properties) {
                    $currentField = $prefix ? $prefix . '.' . $field : $field;
                    if (isset($properties['fields']['keyword'])) {
                        $keywordFields[] = $currentField; // Thêm trường keyword vào mảng
                    }
                    // Kiểm tra các trường con
                    if (isset($properties['properties'])) {
                        $findKeywordFields($properties['properties'], $currentField);
                    }
                }
            };

            $findKeywordFields($data['mappings']['properties']);

            return $keywordFields;
        });
        return $data;
    }
}

// Logging
if (!function_exists('writeAndThrowError')) {
    function writeAndThrowError($mess_write, $e)
    {
        if (preg_match('/Có lỗi/', $e->getMessage())) {
            $mess_write = $mess_write . ' ' . $e->getMessage();
        }
        throw new \Exception($mess_write, 0, $e);
    }
}

if (!function_exists('sendErrorToTelegram')) {
    function sendErrorToTelegram($e)
    {
        $send_error_to_telegram = config('params')['send_error_to_telegram'];
        if ($send_error_to_telegram) {
            $request = request();
            $mess_write = $e->getMessage();
            $token = '';
            $login_name = '';
            $ip = '';
            $hostname = '';
            $path = '';
            $token_header = $request->bearerToken();
            if ($token_header) {
                $token = get_token_header($request, $token_header);
                $login_name = $token->login_name;
                $ip = $request->ip();
                $hostname = gethostbyaddr($ip);
                $path =  $request->path();
            }
            $mess_tele =
                "<b>Thông báo: </b>" . "$mess_write\n"
                . "<b>Path: </b>" . "$path\n"
                . "<b>Loginname: </b>" . "$login_name\n"
                . "<b>Tên máy: </b>" . "$hostname\n"
                . "<b>IP: </b>" . "$ip\n";
            dispatch(new SendTelegramMessageJob($mess_tele));
        }
    }
}

if (!function_exists('logError')) {
    function logError($e, $mess = '')
    {
        $request = request();
        $mess_write = $mess . ' ' . $e->getMessage();
        $token = '';
        $login_name = '';
        $ip = '';
        $hostname = '';
        $path = '';
        $token_header = $request->bearerToken();
        if ($token_header) {
            $token = get_token_header($request, $token_header);
            $login_name = $token->login_name;
            $ip = $request->ip();
            $hostname = gethostbyaddr($ip);
            $path =  $request->path();
        }
        $mess_log = 'Api: ' . $path .
            '; Loginame: ' . $login_name .
            '; Hostname: ' . $hostname .
            '; IP máy: ' . $ip .
            '; Mô tả: ' . $mess_write;
        // Lặp qua chuỗi các ngoại lệ để tìm ngoại lệ gốc
        while ($e->getPrevious()) {
            $e = $e->getPrevious();
        }
        Log::error($mess_log, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => request()->fullUrl(),
            'request_data' => request()->all(),
        ]);
    }
    if (!function_exists('getMucHuongBHYT')) {
        /**
         * Tính mức hưởng BHYT dựa trên mã thẻ và tổng chi phí của lần khám
         *
         * @param $maThe          Mã thẻ BHYT
         * @param float|null $tongChiPhi Tổng chi phí của lần khám
         * @param int $mucLuongCoSo      Mức lương cơ sở hiện tại
         * @return float|null            Tỷ lệ được hưởng (ví dụ: 0.8, 0.95, 1.0)
         */
        function getMucHuongBHYT($maThe, ?float $tongChiPhi = null, $thoiGianXacDinh = 0): ?float
        {
            if (!$thoiGianXacDinh) {
                $thoiGianXacDinh = now()->format('YmdHis');
            }
            $bhytParam = new BhytParam();
            $dataBhytParam = $bhytParam
                ->where(function ($q)  use ($thoiGianXacDinh) {
                    $q->where('from_time', '<=', $thoiGianXacDinh)
                        ->where('to_time', '>=', $thoiGianXacDinh);
                })
                ->orWhere(function ($q)  use ($thoiGianXacDinh) {
                    $q->where('from_time', '<=', $thoiGianXacDinh)
                        ->whereNull('to_time');
                })
                ->first();

            if (!$dataBhytParam) {
                return 0;
            }
            $mucLuongCoSo = (int) $dataBhytParam->base_salary ?? 0;
            $minTotalBySalary = $dataBhytParam->min_total_by_salary ?? 0.15;
            if (!$maThe) {
                return 0;
            }

            $doiTuong = substr($maThe, 0, 2);
            $kyHieu = substr($maThe, 2, 1);

            $mucHuong = [
                '1' => ['phanTram' => 1.0,  'doiTuong' => ['CC', 'TE']],
                '2' => ['phanTram' => 1.0,  'doiTuong' => ['CK', 'CB', 'KC', 'HN', 'DT', 'DK', 'XD', 'BT', 'TS', 'AK', 'CT']],
                '3' => ['phanTram' => 0.95, 'doiTuong' => ['HT', 'TC', 'CN', 'PV', 'TG', 'DS', 'HK']],
                '4' => ['phanTram' => 0.8,  'doiTuong' => ['DN', 'HX', 'CH', 'NN', 'TK', 'HC', 'XK', 'TB', 'NO', 'XB', 'TN', 'CS', 'XN', 'MS', 'HD', 'TQ', 'TA', 'TY', 'HG', 'LS', 'HS', 'SV', 'GB', 'GD', 'ND', 'TH', 'TV', 'TD', 'TU', 'BA']],
            ];

            // Tính mức mặc định nếu không nằm trong danh sách đối tượng cụ thể
            $phanTramMacDinh = match ($kyHieu) {
                '1' => 1.0,
                '2' => 1.0,
                '3' => 0.95,
                '4' => 0.8,
                default => null,
            };

            // Áp dụng mức theo đối tượng nếu trùng
            foreach ($mucHuong as $ky => $info) {
                if ($ky === $kyHieu && in_array($doiTuong, $info['doiTuong'])) {
                    $phanTram = $info['phanTram'];
                    break;
                }
            }

            // Nếu không xác định được, dùng mặc định
            $phanTram ??= $phanTramMacDinh;

            // Áp dụng ngoại lệ: nếu là ký hiệu 3 hoặc 4 và tổng chi phí < 15% mức lương cơ sở thì hưởng 100%
            if (in_array($kyHieu, ['3', '4']) && $tongChiPhi !== null) {
                $nguong = $mucLuongCoSo * $minTotalBySalary;
                if ($tongChiPhi < $nguong) {
                    return 1.0;
                }
            }

            return $phanTram;
        }
    }

    if (!function_exists('getTyLeThanhToanDichVuBHYT')) {
        /**
         * Xác định tỷ lệ thanh toán BHYT của 1 dịch vụ trong 1 bảng kê
         *
         * @param string $maThe          Mã thẻ BHYT 
         * @param string $levelCode      Tuyến
         * @param float $tongChiPhi      Tổng chi phí dịch vụ BHYT trong lần điều trị
         * @param bool $isBHYTCovered    Dịch vụ có nằm trong phạm vi BHYT không?
         * @param bool $isRightRoute     Có đúng tuyến không?
         * @param bool $isEmergency      Có phải cấp cứu không?
         * @param int|string $thoiGianXacDinh  Thời gian xác định (YmdHis)
         * @param float|null $tyLeRiengCuaDV   Tỷ lệ riêng biệt của dịch vụ c
         *
         * @return float Tỷ lệ thanh toán (0 → 1.0)
         */
        function getTyLeThanhToanDichVuBHYT(
            string $maThe,
            string $levelCode,
            float $tongChiPhi,
            bool $isBHYTCovered,
            bool $isRightRoute,
            bool $isEmergency,
            $thoiGianXacDinh,
            ?float $tyLeRiengCuaDV = null
        ): float {
            if (!$isBHYTCovered) {
                return 0.0; // Không thuộc phạm vi hưởng → không thanh toán
            }

            // Bước 1: Tính mức hưởng theo thẻ
            $mucHuong = getMucHuongBHYT($maThe, $tongChiPhi, $thoiGianXacDinh); // Trả về 0.8, 0.95, 1.0

            if ($mucHuong == null) {
                return 0.0;
            }

            // Bước 2: Nếu dịch vụ có tỷ lệ riêng → áp dụng luôn
            if ($tyLeRiengCuaDV !== null) {
                return min($tyLeRiengCuaDV, 1.0); // Không vượt quá 100%
            }

            // Bước 3: Áp dụng theo tuyến và tình trạng cấp cứu
            if ($isRightRoute || $isEmergency) {
                return $mucHuong;
            } else {
                // Trái tuyến
                return getTyLeThanhToanTruongHopTraiTuyen($levelCode, $mucHuong);
            }
        }
    }

    if (!function_exists('getTyLeThanhToanTruongHopTraiTuyen')) {
        function getTyLeThanhToanTruongHopTraiTuyen(string $levelCode, float $mucHuong): float
        {
            return match ($levelCode) {
                '3' => $mucHuong,                    // Tuyến huyện => hưởng 100% mức hưởng
                '2' => round($mucHuong * 0.6, 2),    // Tuyến tỉnh => 60%
                '1' => round($mucHuong * 0.4, 2),    // Tuyến trung ương => 40% (nếu áp dụng)
                default => round($mucHuong * 0.6, 2), // Mặc định giả định tuyến tỉnh
            };
        }
    }
    if (!function_exists('moneyToWords')) {
        function moneyToWords($number): string
        {
            if (!is_numeric($number)) {
                return '';
            }

            if ($number == 0) {
                return 'Không đồng';
            }

            if (!class_exists('NumberFormatter')) {
                throw new \RuntimeException('Thiếu extension intl trong PHP để dùng NumberFormatter.');
            }

            $formatter = new NumberFormatter('vi', NumberFormatter::SPELLOUT);
            $words = $formatter->format($number);

            // Fix một số chữ viết hoa đầu câu và bổ sung hậu tố "đồng"
            return ucfirst($words) . ' đồng';
        }
    }
    if (!function_exists('isEmptyXml')) {
        function isEmptyXml($xmlField)
        {
            $value = trim((string) $xmlField);
            return $value === '';
        }
    }

    if (!function_exists('xepLoaiBMI')) {
        function xepLoaiBMI($bmi = 0)
        {
            if ($bmi < 18.5) return 'Gầy';
            if ($bmi < 25) return 'Bình thường';
            if ($bmi < 30) return 'Thừa cân';
            if ($bmi < 35) return 'Béo phì độ I';
            if ($bmi < 40) return 'Béo phì độ II';
            return 'Béo phì độ III';
        }
    }

    if (!function_exists('getTuoi')) {
    /**
     * Trả về tuổi theo năm, tháng, ngày, giờ từ chuỗi 14 ký tự định dạng YmdHis.
     *
     * @param string $dobString
     * @return array|null
     */
    function getTuoi(string $dobString): ?array
    {
        if (!preg_match('/^\d{14}$/', $dobString)) {
            return null;
        }

        try {
            $dob = \DateTime::createFromFormat('YmdHis', $dobString);
            $now = new \DateTime();

            // Tính chênh lệch
            $diff = $dob->diff($now);

            // Tính thời gian tổng thể
            $intervalInSeconds = $now->getTimestamp() - $dob->getTimestamp();

            return [
                '01'  => $diff->y, // năm
                '02' => $diff->y * 12 + $diff->m, // tháng
                '03'   => floor($intervalInSeconds / (60 * 60 * 24)), // ngày
                '04'  => floor($intervalInSeconds / (60 * 60)), // giờ
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}

}
