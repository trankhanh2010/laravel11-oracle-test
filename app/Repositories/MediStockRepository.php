<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MediStock;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class MediStockRepository
{
    protected $mediStock;
    protected $room;
    public function __construct(MediStock $mediStock, Room $room)
    {
        $this->mediStock = $mediStock;
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->mediStock
            ->leftJoin('his_room as room', 'room.id', '=', 'his_medi_stock.room_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
            ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'room.room_type_id')
            ->leftJoin('his_medi_stock as parent', 'parent.id', '=', 'his_medi_stock.parent_id')
            ->select(
                'his_medi_stock.*',
                'department.department_name',
                'department.department_code',
                'room_type.room_type_name',
                'room_type.room_type_code',
                'parent.medi_stock_name as parent_name',
                'parent.medi_stock_code as parent_code'
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'exp_mest_types:exp_mest_type_code,exp_mest_type_name',
            'imp_mest_types:imp_mest_type_code,imp_mest_type_name'
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock.medi_stock_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medi_stock.medi_stock_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock.is_active'), $isActive);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        switch ($param) {
            case 'khoXuatKeDonThuocPhongKham':
                $query->whereIn('his_medi_stock.medi_stock_code', ['NT', 'KNGT', 'KTD']);
                return $query;
            case 'nhaThuocKeDonThuocPhongKham':
                $query->where('his_medi_stock.IS_DRUG_STORE', 1);
                return $query;
            default:
                return $query;
        }
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['department_name', 'department_code'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['room_type_name', 'room_type_code'])) {
                        $query->orderBy('room_type.' . $key, $item);
                    }
                    if (in_array($key, ['parent_name', 'parent_code'])) {
                        $query->orderBy('parent.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_medi_stock.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->mediStock->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $room = $this->room::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'department_id' => $request->department_id,
            'room_type_id' => $request->room_type_id,
        ]);
        $data = $this->mediStock::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
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
                $dataToSync_medi_stock_exty[$id]['create_time'] = now()->format('YmdHis');
                $dataToSync_medi_stock_exty[$id]['modify_time'] = now()->format('YmdHis');
                $dataToSync_medi_stock_exty[$id]['creator'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_medi_stock_exty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_medi_stock_exty[$id]['app_creator'] = $appCreator;
                $dataToSync_medi_stock_exty[$id]['app_modifier'] = $appModifier;
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
                $dataToSync_medi_stock_imty[$id]['create_time'] = now()->format('YmdHis');
                $dataToSync_medi_stock_imty[$id]['modify_time'] = now()->format('YmdHis');
                $dataToSync_medi_stock_imty[$id]['creator'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_medi_stock_imty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_medi_stock_imty[$id]['app_creator'] = $appCreator;
                $dataToSync_medi_stock_imty[$id]['app_modifier'] = $appModifier;
                $dataToSync_medi_stock_imty[$id]['is_auto_approve'] = $item->is_auto_approve;
                $dataToSync_medi_stock_imty[$id]['is_auto_execute'] = $item->is_auto_execute;
            }
            $data->imp_mest_types()->sync($dataToSync_medi_stock_imty);
        }
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $room_update = [
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'department_id' => $request->department_id,
            'room_type_id' => $request->room_type_id,
            'is_active' => $request->is_active,
        ];
        $data_update = [
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
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
        $room = $this->room->find($data->room_id);
        $room->fill($room_update);
        $room->save();
        $data->fill($data_update);
        $data->save();
        if ($request->medi_stock_exty !== null) {
            $dataToSync_medi_stock_exty = [];
            foreach ($request->medi_stock_exty as $item) {
                $id = $item->id;
                $dataToSync_medi_stock_exty[$id] = [];
                $dataToSync_medi_stock_exty[$id]['modify_time'] = now()->format('YmdHis');
                $dataToSync_medi_stock_exty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_medi_stock_exty[$id]['app_modifier'] = $appModifier;
                $dataToSync_medi_stock_exty[$id]['is_auto_approve'] = $item->is_auto_approve;
                $dataToSync_medi_stock_exty[$id]['is_auto_execute'] = $item->is_auto_execute;
            }
            $data->exp_mest_types()->sync($dataToSync_medi_stock_exty);
        } else {
            $data->exp_mest_types()->sync([]);
        }
        if ($request->medi_stock_imty !== null) {
            $dataToSync_medi_stock_imty = [];
            foreach ($request->medi_stock_imty as $item) {
                $id = $item->id;
                $dataToSync_medi_stock_imty[$id] = [];
                $dataToSync_medi_stock_imty[$id]['modify_time'] = now()->format('YmdHis');
                $dataToSync_medi_stock_imty[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_medi_stock_imty[$id]['app_modifier'] = $appModifier;
                $dataToSync_medi_stock_imty[$id]['is_auto_approve'] = $item->is_auto_approve;
                $dataToSync_medi_stock_imty[$id]['is_auto_execute'] = $item->is_auto_execute;
            }
            $data->imp_mest_types()->sync($dataToSync_medi_stock_imty);
        } else {
            $data->imp_mest_types()->sync([]);
        }
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function delete($data)
    {
        DB::connection('oracle_his')->beginTransaction();
        $data->delete();
        $room = $this->room->find($data->room_id);
        $room->delete();
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_medi_stock.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_medi_stock.id');
            $maxId = $this->applyJoins()->max('his_medi_stock.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('medi_stock', 'his_medi_stock', $startId, $endId, $batchSize, $this->paramWith());
            }
        }
    }
}
