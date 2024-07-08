<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\MediStock\CreateMediStockRequest;
use App\Http\Requests\MediStock\UpdateMediStockRequest;
use App\Models\HIS\MediStock;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;
use App\Models\HIS\Room;
class MediStockController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->medi_stock = new MediStock();
        $this->room = new Room();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->medi_stock->getConnection()->getSchemaBuilder()->hasColumn($this->medi_stock->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

    }
    public function medi_stock($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword !== null) {
            $param = [
                'room:id,department_id,room_type_id',
                'room.department:id,department_name,department_code',
                'room.room_type:id,room_type_name,room_type_code',
                'parent:id,medi_stock_name,medi_stock_code',
                'exp_mest_types',
                'imp_mest_types',
            ];
            $data = $this->medi_stock;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(medi_stock_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(medi_stock_name)'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
            });
        } 
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->medi_stock_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring;
                $param = [
                    'room:id,department_id,room_type_id',
                    'room.department:id,department_name,department_code',
                    'room.room_type:id,room_type_name,room_type_code',
                    'parent:id,medi_stock_name,medi_stock_code',
                    'exp_mest_types',
                    'imp_mest_types',
                ];
            } else {
                if ($id != 'deleted') {
                    if (!is_numeric($id)) {
                        return return_id_error($id);
                    }
                    $data = $this->medi_stock->find($id);
                    if ($data == null) {
                        return return_not_record($id);
                    }
                }
                $name = $this->medi_stock_name . '_' . $id;
                $param = [
                    'room',
                    'room.department',
                    'room.room_type',
                    'parent',
                    'exp_mest_types',
                    'imp_mest_types',
                ];
            }
            $data = get_cache_full($this->medi_stock, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }

    public function medi_stock_restore($id = null, Request $request)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->medi_stock::withDeleted()->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $room = $this->room::withDeleted()->find($data->room_id);
        if ($room == null) {
            return return_not_record($data->room_id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'is_delete' => 0,
        ];
        $room_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'is_delete' => 0,
        ];
        $data->fill($data_update);
        $data->save();
        $room->fill($room_update);
        $room->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->medi_stock_name));
        return redirect()->route('HIS.Desktop.Plugins.HisMediStock.api.medi_stock.index');
    }

    public function medi_stock_create(CreateMediStockRequest $request)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $room = $this->room::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'department_id' => $request->department_id,
                'room_type_id' => $request->room_type_id,

            ]);
            $data = $this->medi_stock::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'medi_stock_code' => $request->medi_stock_code,
                'medi_stock_name' => $request->medi_stock_name,
                'bhyt_head_code' => $request->bhyt_head_code,
                'not_in_bhyt_head_code' => $request->not_in_bhyt_head_code,
                'parent_id' => $request->parent_id,
                'is_allow_imp_supplier' => $request->is_allow_imp_supplier,
                'do_not_imp_medicine' => $request->do_not_imp_medicine,
                'do_not_imp_material' => $request->do_not_imp_material,
                'is_odd' => $request->is_odd,
                'is_blood' => $request->is_blood,
                'is_show_ddt' => $request->is_show_ddt,
                'is_planning_trans_as_default' => $request->is_planning_trans_as_default,
                'is_auto_create_chms_imp' => $request->is_auto_create_chms_imp,
                'is_auto_create_reusable_imp' => $request->is_auto_create_reusable_imp,
                'is_goods_restrict' => $request->is_goods_restrict,
                'is_show_inpatient_return_pres' => $request->is_show_inpatient_return_pres,
                'is_moba_change_amount' => $request->is_moba_change_amount,
                'is_for_rejected_moba' => $request->is_for_rejected_moba,
                'is_show_anticipate' => $request->is_show_anticipate,
                'is_cabinet' => $request->is_cabinet,
                'is_new_medicine' => $request->is_new_medicine,
                'is_traditional_medicine' => $request->is_traditional_medicine,
                'is_drug_store' => $request->is_drug_store,
                'is_show_drug_store' => $request->is_show_drug_store,
                'is_business' => $request->is_business,
                'is_expend' => $request->is_expend,
                'patient_classify_ids' => $request->patient_classify_ids,
                'cabinet_manage_option' => $request->cabinet_manage_option,
                'room_id' => $room->id,
            ]);
            if ($request->medi_stock_exty !== null) {
                $dataToSync_medi_stock_exty = [];
                foreach ($request->medi_stock_exty as $item) {
                    $id = $item->id;
                    $dataToSync_medi_stock_exty[$id] = [];
                    $dataToSync_medi_stock_exty[$id]['create_time'] = now()->format('Ymdhis');
                    $dataToSync_medi_stock_exty[$id]['modify_time'] = now()->format('Ymdhis');
                    $dataToSync_medi_stock_exty[$id]['creator'] = get_loginname_with_token($request->bearerToken(), $this->time);
                    $dataToSync_medi_stock_exty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $this->time);
                    $dataToSync_medi_stock_exty[$id]['app_creator'] = $this->app_creator;
                    $dataToSync_medi_stock_exty[$id]['app_modifier'] = $this->app_modifier;
                    $dataToSync_medi_stock_exty[$id]['is_auto_approve'] = $item->is_auto_approve;
                    $dataToSync_medi_stock_exty[$id]['is_auto_execute'] = $item->is_auto_execute;
                }
                $data->exp_mest_types()->sync($dataToSync_medi_stock_exty);
            }
            if ($request->medi_stock_imty !== null) {
                $dataToSync_medi_stock_imty = [];
                foreach ($request->medi_stock_imty as $item) {
                    $id = $item->id;
                    $dataToSync_medi_stock_imty[$id] = [];
                    $dataToSync_medi_stock_imty[$id]['create_time'] = now()->format('Ymdhis');
                    $dataToSync_medi_stock_imty[$id]['modify_time'] = now()->format('Ymdhis');
                    $dataToSync_medi_stock_imty[$id]['creator'] = get_loginname_with_token($request->bearerToken(), $this->time);
                    $dataToSync_medi_stock_imty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $this->time);
                    $dataToSync_medi_stock_imty[$id]['app_creator'] = $this->app_creator;
                    $dataToSync_medi_stock_imty[$id]['app_modifier'] = $this->app_modifier;
                    $dataToSync_medi_stock_imty[$id]['is_auto_approve'] = $item->is_auto_approve;
                    $dataToSync_medi_stock_imty[$id]['is_auto_execute'] = $item->is_auto_execute;
                }
                $data->imp_mest_types()->sync($dataToSync_medi_stock_imty);
            }
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->medi_stock_name));
            return return_data_create_success([$data, $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function medi_stock_update(UpdateMediStockRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->medi_stock->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $room = $this->room->find($data->room_id);
        if ($room == null) {
            return return_not_record($data->room_id);
        }
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $room_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'department_id' => $request->department_id,
                'room_type_id' => $request->room_type_id,
                'is_active' => $request->is_active,

            ];
            $data_update = [
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,
                'medi_stock_code' => $request->medi_stock_code,
                'medi_stock_name' => $request->medi_stock_name,
                'bhyt_head_code' => $request->bhyt_head_code,
                'not_in_bhyt_head_code' => $request->not_in_bhyt_head_code,
                'parent_id' => $request->parent_id,
                'is_allow_imp_supplier' => $request->is_allow_imp_supplier,
                'do_not_imp_medicine' => $request->do_not_imp_medicine,
                'do_not_imp_material' => $request->do_not_imp_material,
                'is_odd' => $request->is_odd,
                'is_blood' => $request->is_blood,
                'is_show_ddt' => $request->is_show_ddt,
                'is_planning_trans_as_default' => $request->is_planning_trans_as_default,
                'is_auto_create_chms_imp' => $request->is_auto_create_chms_imp,
                'is_auto_create_reusable_imp' => $request->is_auto_create_reusable_imp,
                'is_goods_restrict' => $request->is_goods_restrict,
                'is_show_inpatient_return_pres' => $request->is_show_inpatient_return_pres,
                'is_moba_change_amount' => $request->is_moba_change_amount,
                'is_for_rejected_moba' => $request->is_for_rejected_moba,
                'is_show_anticipate' => $request->is_show_anticipate,
                'is_cabinet' => $request->is_cabinet,
                'is_new_medicine' => $request->is_new_medicine,
                'is_traditional_medicine' => $request->is_traditional_medicine,
                'is_drug_store' => $request->is_drug_store,
                'is_show_drug_store' => $request->is_show_drug_store,
                'is_business' => $request->is_business,
                'is_expend' => $request->is_expend,
                'patient_classify_ids' => $request->patient_classify_ids,
                'cabinet_manage_option' => $request->cabinet_manage_option,
                'is_active' => $request->is_active,

            ];
            $room->fill($room_update);
            $room->save();
            $data->fill($data_update);
            $data->save();
            if ($request->medi_stock_exty !== null) {
                $dataToSync_medi_stock_exty = [];
                foreach ($request->medi_stock_exty as $item) {
                    $id = $item->id;
                    $dataToSync_medi_stock_exty[$id] = [];
                    $dataToSync_medi_stock_exty[$id]['modify_time'] = now()->format('Ymdhis');
                    $dataToSync_medi_stock_exty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $this->time);
                    $dataToSync_medi_stock_exty[$id]['app_modifier'] = $this->app_modifier;
                    $dataToSync_medi_stock_exty[$id]['is_auto_approve'] = $item->is_auto_approve;
                    $dataToSync_medi_stock_exty[$id]['is_auto_execute'] = $item->is_auto_execute;
                }
                $data->exp_mest_types()->sync($dataToSync_medi_stock_exty);
            }
            if ($request->medi_stock_imty !== null) {
                $dataToSync_medi_stock_imty = [];
                foreach ($request->medi_stock_imty as $item) {
                    $id = $item->id;
                    $dataToSync_medi_stock_imty[$id] = [];
                    $dataToSync_medi_stock_imty[$id]['modify_time'] = now()->format('Ymdhis');
                    $dataToSync_medi_stock_imty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $this->time);
                    $dataToSync_medi_stock_imty[$id]['app_modifier'] = $this->app_modifier;
                    $dataToSync_medi_stock_imty[$id]['is_auto_approve'] = $item->is_auto_approve;
                    $dataToSync_medi_stock_imty[$id]['is_auto_execute'] = $item->is_auto_execute;
                }
                $data->imp_mest_types()->sync($dataToSync_medi_stock_imty);
            }
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->medi_stock_name));
            return return_data_create_success([$data, $room]);
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }

    public function medi_stock_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->medi_stock->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $room = $this->room->find($data->room_id);
        if ($room == null) {
            return return_not_record($data->room_id);
        }
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        try {
            $data->delete();
            $room->delete();
            DB::connection('oracle_his')->commit();
            // Gọi event để xóa cache
            event(new DeleteCache($this->medi_stock_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            // Rollback transaction nếu có lỗi
            DB::connection('oracle_his')->rollBack();
            return return_data_fail_transaction();
        }
    }
}
