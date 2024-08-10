<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\MedicinePaty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MedicinePatyController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->medicine_paty = new MedicinePaty();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->medicine_paty);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function medicine_paty($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->medicine_paty
                ->leftJoin('his_medicine as medicine', 'medicine.id', '=', 'his_medicine_paty.medicine_id')
                ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'medicine.medicine_type_id')
                ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_medicine_paty.patient_type_id')
                    ->select(
                        'his_medicine_paty.*',
                        'medicine_type.medicine_type_code',
                        'medicine_type.medicine_type_name',
                        'patient_type.patient_type_code',
                        'patient_type.patient_type_name',

                        'medicine.CONTRACT_PRICE',
                        'medicine.TAX_RATIO',
                        
                        'medicine.EXPIRED_DATE',
                        'medicine.TDL_BID_NUMBER',
                        'medicine.TDL_BID_NUM_ORDER',

                        'medicine.IMP_TIME',
                        'medicine.IMP_VAT_RATIO',
                        'medicine.IMP_PRICE',
                        'medicine.VIR_IMP_PRICE',
                        'medicine.INTERNAL_PRICE'
                        
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('medicine_type.medicine_type_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_paty.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_medicine_paty.' . $key, $item);
                    }
                }
                if($this->get_all){
                    $data = $data
                    ->get();
                }else{
                    $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->medicine_paty_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->medicine_paty
                        ->leftJoin('his_medicine as medicine', 'medicine.id', '=', 'his_medicine_paty.medicine_id')
                        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'medicine.medicine_type_id')
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_medicine_paty.patient_type_id')
                            ->select(
                                'his_medicine_paty.*',
                                'medicine_type.medicine_type_code',
                                'medicine_type.medicine_type_name',
                                'patient_type.patient_type_code',
                                'patient_type.patient_type_name',
        
                                'medicine.CONTRACT_PRICE',
                                'medicine.TAX_RATIO',
                                
                                'medicine.EXPIRED_DATE',
                                'medicine.TDL_BID_NUMBER',
                                'medicine.TDL_BID_NUM_ORDER',
        
                                'medicine.IMP_TIME',
                                'medicine.IMP_VAT_RATIO',
                                'medicine.IMP_PRICE',
                                'medicine.VIR_IMP_PRICE',
                                'medicine.INTERNAL_PRICE'
                                
                            );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_paty.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_medicine_paty.' . $key, $item);
                            }
                        }
                        if($this->get_all){
                            $data = $data
                            ->get();
                        }else{
                            $data = $data
                            ->skip($this->start)
                            ->take($this->limit)
                            ->get();
                        }
                        return ['data' => $data, 'count' => $count];
                    });
                } else {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $check_id = $this->check_id($id, $this->medicine_paty, $this->medicine_paty_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->medicine_paty_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->medicine_paty
                        ->leftJoin('his_medicine as medicine', 'medicine.id', '=', 'his_medicine_paty.medicine_id')
                        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'medicine.medicine_type_id')
                        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_medicine_paty.patient_type_id')
                            ->select(
                                'his_medicine_paty.*',
                                'medicine_type.medicine_type_code',
                                'medicine_type.medicine_type_name',
                                'patient_type.patient_type_code',
                                'patient_type.patient_type_name',
        
                                'medicine.CONTRACT_PRICE',
                                'medicine.TAX_RATIO',
                                
                                'medicine.EXPIRED_DATE',
                                'medicine.TDL_BID_NUMBER',
                                'medicine.TDL_BID_NUM_ORDER',
        
                                'medicine.IMP_TIME',
                                'medicine.IMP_VAT_RATIO',
                                'medicine.IMP_PRICE',
                                'medicine.VIR_IMP_PRICE',
                                'medicine.INTERNAL_PRICE'
                                
                            )
                            ->where('his_medicine_paty.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_medicine_paty.is_active'), $this->is_active);
                            });
                        }
                        $data = $data->first();
                        return $data;
                    });
                }
            }
            $param_return = [
                $this->get_all_name => $this->get_all,
                $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
                $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
                $this->count_name => $count ?? ($data['count'] ?? null),
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
    // /// Medicine Paty
    // public function medicine_paty($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->medicine_paty_name;
    //         $param = [
    //             'medicine',
    //             'medicine.medicine_type:id,medicine_type_name,medicine_type_code',
    //             'patient_type'
    //         ];
    //     } else {
    //         $name = $this->medicine_paty_name . '_' . $id;
    //         $param = [
    //             'medicine',
    //             'medicine.medicine_type',
    //             'patient_type'
    //         ];
    //     }
    //     $data = get_cache_full($this->medicine_paty, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
}
