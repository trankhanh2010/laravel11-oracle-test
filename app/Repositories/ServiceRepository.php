<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Service;
use App\Models\HIS\ServiceGroup;
use App\Models\HIS\ServicePaty;
use App\Models\HIS\ServiceRoom;
use App\Models\HIS\ServSegr;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceRepository
{
    protected $service;
    protected $servicePaty;
    protected $servSegr;
    protected $serviceGroup;
    protected $serviceRoom;
    public function __construct(
        Service $service,
        ServicePaty $servicePaty,
        ServSegr $servSegr,
        ServiceGroup $serviceGroup,
        ServiceRoom $serviceRoom,
    ) {
        $this->service = $service;
        $this->servicePaty = $servicePaty;
        $this->servSegr = $servSegr;
        $this->serviceGroup = $serviceGroup;
        $this->serviceRoom = $serviceRoom;
    }

    public function applyJoins()
    {
        return $this->service
            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'his_service.service_type_id')
            ->leftJoin('his_service as parent', 'parent.id', '=', 'his_service.parent_id')
            ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'his_service.service_unit_id')
            ->leftJoin('his_hein_service_type as hein_service_type', 'hein_service_type.id', '=', 'his_service.hein_service_type_id')
            ->leftJoin('his_patient_type as bill_patient_type', 'bill_patient_type.id', '=', 'his_service.bill_patient_type_id')
            ->leftJoin('his_pttt_group as pttt_group', 'pttt_group.id', '=', 'his_service.pttt_group_id')
            ->leftJoin('his_pttt_method as pttt_method', 'pttt_method.id', '=', 'his_service.pttt_method_id')
            ->leftJoin('his_icd_cm as icd_cm', 'icd_cm.id', '=', 'his_service.icd_cm_id')
            ->leftJoin('his_department as revenue_department', 'revenue_department.id', '=', 'his_service.revenue_department_id')
            ->leftJoin('his_package as package', 'package.id', '=', 'his_service.package_id')
            ->leftJoin('his_exe_service_module as exe_service_module', 'exe_service_module.id', '=', 'his_service.exe_service_module_id')
            ->leftJoin('his_gender as gender', 'gender.id', '=', 'his_service.gender_id')
            ->leftJoin('his_ration_group as ration_group', 'ration_group.id', '=', 'his_service.ration_group_id')
            ->leftJoin('his_diim_type as diim_type', 'diim_type.id', '=', 'his_service.diim_type_id')
            ->leftJoin('his_fuex_type as fuex_type', 'fuex_type.id', '=', 'his_service.fuex_type_id')
            ->leftJoin('his_test_type as test_type', 'test_type.id', '=', 'his_service.test_type_id')
            ->leftJoin('his_other_pay_source as other_pay_source', 'other_pay_source.id', '=', 'his_service.other_pay_source_id')
            ->leftJoin('his_film_size as film_size', 'film_size.id', '=', 'his_service.film_size_id')
            ->leftJoin('his_patient_type as default_patient_type', 'default_patient_type.id', '=', 'his_service.default_patient_type_id')
            ->select(
                'his_service.*',
                'service_type.service_type_code',
                'service_type.service_type_name',
                'parent.service_code as parent_service_code',
                'parent.service_name as parent_service_name',
                'service_unit.service_unit_code',
                'service_unit.service_unit_name',
                'hein_service_type.hein_service_type_code',
                'hein_service_type.hein_service_type_name',
                'bill_patient_type.patient_type_code as bill_patient_type_code',
                'bill_patient_type.patient_type_name as bill_patient_type_name',
                'pttt_group.pttt_group_code',
                'pttt_group.pttt_group_name',
                'pttt_method.pttt_method_code',
                'pttt_method.pttt_method_name',
                'icd_cm.icd_cm_code',
                'icd_cm.icd_cm_name',
                'revenue_department.department_code as revenue_department_code',
                'revenue_department.department_name as revenue_department_name',
                'package.package_code',
                'package.package_name',
                'exe_service_module.exe_service_module_name',
                'gender.gender_code',
                'gender.gender_name',
                'ration_group.ration_group_code',
                'ration_group.ration_group_name',
                'diim_type.diim_type_code',
                'diim_type.diim_type_name',
                'fuex_type.fuex_type_code',
                'fuex_type.fuex_type_name',
                'test_type.test_type_code',
                'test_type.test_type_name',
                'other_pay_source.other_pay_source_code',
                'other_pay_source.other_pay_source_name',
                'film_size.film_size_code',
                'film_size.film_size_name',
                'default_patient_type.patient_type_code as default_patient_type_code',
                'default_patient_type.patient_type_name as default_patient_type_name',
            );
    }
    public function applyJoinsDichVuChiDinh()
    {
        return $this->service
            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'his_service.service_type_id')
            ->leftJoin('his_service as parent', 'parent.id', '=', 'his_service.parent_id')
            ->leftJoin('his_pttt_group as pttt_group', 'pttt_group.id', '=', 'his_service.pttt_group_id')
            ->leftJoin('his_package_detail as package_detail', function ($join) {
                $join->on('package_detail.package_id', '=', 'his_service.package_id')
                    ->on('package_detail.service_id', '=', 'his_service.id');
            })
            ->select(
                'his_service.id as key',
                'his_service.id',
                'his_service.service_code',
                'his_service.HEIN_SERVICE_BHYT_CODE',
                'his_service.service_name',

                'pttt_group.pttt_group_code',
                'pttt_group.pttt_group_name',

                'his_service.NOTICE',
                'his_service.IS_ALLOW_EXPEND', // cho phép tích hao phí
                'his_service.IS_OUT_PARENT_FEE',

                'package_detail.AMOUNT  as package_detail_AMOUNT',
                'his_service.DO_NOT_USE_BHYT', // không cho hưởng bhyt khi chỉ định từ phòng khám

                'service_type.service_type_code',
                'service_type.service_type_name',

                'his_service.parent_id',
                'parent.service_code as parent_service_code',
                'parent.service_name as parent_service_name',

                'his_service.is_leaf',
                'his_service.default_patient_type_id',
            );
    }

    public function applyJoinsKeDonThuocPhongKham()
    {
        // Lấy nhóm đầu
        $danhMucChaIds = $this->service
            ->leftJoin('his_service_type', 'his_service_type.id', '=', 'his_service.service_type_id')
            ->where('his_service_type.service_type_code','TH')
            ->whereNull('his_service.parent_id')
            ->whereNull('his_service.is_leaf')
            ->pluck('his_service.id')->toArray();
        // Lấy nhóm thứ 2
        $parentIds = $this->service
            ->whereIn('his_service.parent_id', $danhMucChaIds)
            ->whereNull('his_service.is_leaf')
            ->pluck('his_service.id')->toArray();
                    dd($parentIds);

        return $this->service
            ->join('his_service as parent', function ($join) use ($parentIds) {
                $join->on('parent.id', '=', 'his_service.parent_id')
                    ->whereIn('parent.id', $parentIds);  // Chỉ lấy ra các lá đã được join thằng cha
            })
            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'his_service.service_type_id')
            ->select([
                'his_service.id as key',
                'his_service.id',
                'his_service.service_code',
                'his_service.service_name',
                'his_service.parent_id',
                'his_service.is_leaf',
                'parent.service_code as parent_code',
                'parent.service_name as parent_name',
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service.service_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_service.service_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service.is_active'), $isActive);
        }
        return $query;
    }
    public function applyServiceTypeIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service.service_type_id'), $id);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        switch ($param) {
            case 'chiDinhDichVuKyThuat':
                $query->whereIn('service_type.service_type_code', ['HA', 'GI', 'CL', 'KH', 'NS', 'PT', 'SA', 'CN', 'TT', 'XN'])
                    ->where('his_service.is_active', 1)
                    ->where('his_service.is_delete', 0);
                return $query;
            case 'keDonThuocPhongKham':
                $query->whereIn('service_type.service_type_code', ['TH'])
                    ->whereNotNull('his_service.parent_id')
                    ->where('his_service.is_leaf', 1)
                    ->where('his_service.is_active', 1)
                    ->where('his_service.is_delete', 0);
                return $query;
            default:
                return $query;
        }
    }
    public function applyServiceGroupIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->join('his_serv_segr as serv_segr', 'serv_segr.service_id', '=', 'his_service.id')
                ->whereIn('serv_segr.service_group_id', (array) $param);
        }
        return $query;
    }
    public function checkServiceGroupIds($servicePatyIds)
    {
        $data = [
            'servicePatyIds' => $servicePatyIds,
        ];
        $rules = [];

        $validator = Validator::make($data, $rules);
        $validator->after(function ($validator) use ($servicePatyIds) {
            foreach ($servicePatyIds as $index => $value) {
                $name = $this->serviceGroup->find($value)->service_group_name ?? '';
                $serviceIds = $this->servSegr->where('service_group_id', $value)->pluck('service_id')->toArray();
                if(!$serviceIds){
                        $validator->errors()->add("servicePatyIds", "Nhóm dịch vụ $name không chứa dịch vụ nào!");
                }else{
                    $exitServicePaty = $this->servicePaty
                    ->whereIn('service_id', $serviceIds)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->exists(); 
                    if(!$exitServicePaty){
                        $validator->errors()->add("servicePatyIds", "Nhóm dịch vụ $name chứa các dịch vụ không tồn tại chính sách giá!");
                    }

                    $exitServiceRoom = $this->serviceRoom
                    ->whereIn('service_id', $serviceIds)
                    ->where('is_active', 1)
                    ->where('is_delete', 0)
                    ->exists(); 
                    if(!$exitServiceRoom){
                        $validator->errors()->add("servicePatyIds", "Nhóm dịch vụ $name chứa các dịch vụ không tồn tại cấu hình dịch vụ phòng!");
                    }
                }

            }
        });
        if ($validator->fails()) {

            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'Dữ liệu không hợp lệ!',
                'data'      => $validator->errors()
            ], 422));
        }
    }
    public function applyGroupByField($data, $groupByFields = [])
    {
        if (empty($groupByFields)) {
            return $data;
        }

        // Chuyển các field thành snake_case trước khi nhóm
        $fieldMappings = [];
        foreach ($groupByFields as $field) {
            $snakeField = Str::snake($field);
            $fieldMappings[$snakeField] = $field;
        }

        $snakeFields = array_keys($fieldMappings);

        // Đệ quy nhóm dữ liệu theo thứ tự fields đã convert
        $groupData = function ($items, $fields) use (&$groupData, $fieldMappings) {
            if (empty($fields)) {
                return $items->values(); // Hết field nhóm -> Trả về danh sách gốc
            }

            $currentField = array_shift($fields);
            $originalField = $fieldMappings[$currentField];

            return $items->groupBy(function ($item) use ($currentField) {
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField) {
                $result =  [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                ];
                $result['children'] = $groupData($group, $fields);
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }
    public function buildTreeGroupByServiceTypeName($collection)
    {
        // Nhóm theo serviceTypeName
        $grouped = $collection->groupBy('service_type_name');

        $result = collect();

        foreach ($grouped as $typeName => $group) {
            // Chuẩn hóa từng item trước khi build cây
            $processedGroup = $group->map(function ($item) {
                if ($item->is_leaf) {
                    return $item; // giữ nguyên nếu là lá
                }
                // bỏ các trường thừa
                unset(
                    $item->hein_service_bhyt_code,
                    $item->pttt_group_code,
                    $item->pttt_group_name,
                    $item->notice,
                    $item->is_allow_expend,
                    $item->is_out_parent_fee,
                    $item->package_detail_amount,
                    $item->do_not_use_bhyt,
                    $item->service_type_code,
                    $item->service_type_name,
                    $item->parent_service_code,
                    $item->parent_service_name,
                );
                return $item;
            });
            // Duyệt từng nhóm và build cây theo parent_id
            $tree = $this->buildTreeByParentId($processedGroup);

            $result->push([
                'key' => (string) $typeName,
                'serviceTypeName' => $typeName,
                'total' => $group->count(),
                'children' => $tree,
            ]);
        }

        return $result;
    }
    public function buildTreeByParentId($collection)
    {
        // nhóm đệ qui
        $items = $collection->keyBy('id');
        $tree = collect();

        foreach ($items as $item) {
            if (empty($item->parent_id) && ($item->is_leaf == null)) {
                $tree->push($item);
            } else {
                $parentId = $item->parent_id;
                if ($items->has($parentId) && ($items[$parentId]->is_leaf == null)) {
                    // Gán children object-style
                    $items[$parentId]->children ??= collect();
                    $items[$parentId]->children->push($item);

                    // Gán children trống nếu k phải là lá
                    if ($item->is_leaf == null) {
                        $item->children = collect();
                    }
                }
            }
        }

        return $tree;
    }


    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['parent_name', 'parent_code'])) {
                        $query->orderBy($key, $item);
                    }
                    if (in_array($key, ['service_type_code', 'service_type_name'])) {
                        $query->orderBy('service_type.' . $key, $item);
                    }
                    if (in_array($key, ['parent_service_code', 'parent_service_name'])) {
                        $query->orderBy('parent.' . $key, $item);
                    }
                    if (in_array($key, ['service_unit_code', 'service_unit_name'])) {
                        $query->orderBy('service_unit.' . $key, $item);
                    }
                    if (in_array($key, ['hein_service_type_code', 'hein_service_type_name'])) {
                        $query->orderBy('hein_service_type.' . $key, $item);
                    }
                    if (in_array($key, ['bill_patient_type_code', 'bill_patient_type_name'])) {
                        $query->orderBy('bill_patient_type.' . $key, $item);
                    }
                    if (in_array($key, ['pttt_group_code', 'pttt_group_name'])) {
                        $query->orderBy('pttt_group.' . $key, $item);
                    }
                    if (in_array($key, ['pttt_method_code', 'pttt_method_name'])) {
                        $query->orderBy('pttt_method.' . $key, $item);
                    }
                    if (in_array($key, ['icd_cm_code', 'icd_cm_name'])) {
                        $query->orderBy('icd_cm.' . $key, $item);
                    }
                    if (in_array($key, ['revenue_department_code', 'revenue_department_name'])) {
                        $query->orderBy('revenue_department.' . $key, $item);
                    }
                    if (in_array($key, ['package_code', 'package_name'])) {
                        $query->orderBy('package.' . $key, $item);
                    }
                    if (in_array($key, ['exe_service_module_name'])) {
                        $query->orderBy('exe_service_module.' . $key, $item);
                    }
                    if (in_array($key, ['gender_code', 'gender_name'])) {
                        $query->orderBy('gender.' . $key, $item);
                    }
                    if (in_array($key, ['ration_group_code', 'ration_group_name'])) {
                        $query->orderBy('ration_group.' . $key, $item);
                    }
                    if (in_array($key, ['diim_type_code', 'diim_type_name'])) {
                        $query->orderBy('diim_type.' . $key, $item);
                    }
                    if (in_array($key, ['fuex_type_code', 'fuex_type_name'])) {
                        $query->orderBy('fuex_type.' . $key, $item);
                    }
                    if (in_array($key, ['test_type_code', 'test_type_name'])) {
                        $query->orderBy('test_type.' . $key, $item);
                    }
                    if (in_array($key, ['other_pay_source_code', 'other_pay_source_name'])) {
                        $query->orderBy('other_pay_source.' . $key, $item);
                    }
                    if (in_array($key, ['film_size_code', 'film_size_name'])) {
                        $query->orderBy('film_size.' . $key, $item);
                    }
                    if (in_array($key, ['default_patient_type_code', 'default_patient_type_name'])) {
                        $query->orderBy('default_patient_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service.' . $key, $item);
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
        return $this->service->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->service::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'service_type_id' => $request->service_type_id,
            'service_code' => $request->service_code,
            'service_name' => $request->service_name,
            'service_unit_id' => $request->service_unit_id,
            'speciality_code' => $request->speciality_code,
            'hein_service_type_id' => $request->hein_service_type_id,

            'hein_service_bhyt_code' => $request->hein_service_bhyt_code,
            'hein_service_bhyt_name' => $request->hein_service_bhyt_name,
            'hein_order' => $request->hein_order,
            'parent_id' => $request->parent_id,
            'package_id' => $request->package_id,
            'package_price' => $request->package_price,

            'bill_option' => $request->bill_option,
            'bill_patient_type_id' => $request->bill_patient_type_id,
            'pttt_method_id' => $request->pttt_method_id,
            'is_not_change_bill_paty' => $request->is_not_change_bill_paty,
            'applied_patient_classify_ids' => $request->applied_patient_classify_ids,
            'applied_patient_type_ids' => $request->applied_patient_type_ids,

            'testing_technique' => $request->testing_technique,
            'default_patient_type_id' => $request->default_patient_type_id,
            'pttt_group_id' => $request->pttt_group_id,
            'hein_limit_price_old' => $request->hein_limit_price_old,
            'icd_cm_id' => $request->icd_cm_id,
            'hein_limit_price_in_time' => $request->hein_limit_price_in_time,

            'hein_limit_price' => $request->hein_limit_price,
            'cogs' => $request->cogs,
            'ration_symbol' => $request->ration_symbol,
            'ration_group_id' => $request->ration_group_id,
            'num_order' => $request->num_order,
            'pacs_type_code' => $request->pacs_type_code,

            'diim_type_id' => $request->diim_type_id,
            'fuex_type_id' => $request->fuex_type_id,
            'test_type_id' => $request->test_type_id,
            'sample_type_code' => $request->sample_type_code,
            'max_expend' => $request->max_expend,
            'number_of_film' => $request->number_of_film,

            'film_size_id' => $request->film_size_id,
            'min_process_time' => $request->min_process_time,
            'min_proc_time_except_paty_ids' => $request->min_proc_time_except_paty_ids,
            'estimate_duration' => $request->estimate_duration,
            'max_process_time' => $request->max_process_time,
            'max_proc_time_except_paty_ids' => $request->max_proc_time_except_paty_ids,

            'age_from' => $request->age_from,
            'age_to' => $request->age_to,
            'max_total_process_time' => $request->max_total_process_time,
            'total_time_except_paty_ids' => $request->total_time_except_paty_ids,
            'gender_id' => $request->gender_id,
            'min_duration' => $request->min_duration,

            'max_amount' => $request->max_amount,
            'body_part_ids' => $request->body_part_ids,
            'capacity' => $request->capacity,
            'warning_sampling_time' => $request->warning_sampling_time,
            'exe_service_module_id' => $request->exe_service_module_id,
            'suim_index_id' => $request->suim_index_id,

            'is_kidney' => $request->is_kidney,
            'is_antibiotic_resistance' => $request->is_antibiotic_resistance,
            'is_disallowance_no_execute' => $request->is_disallowance_no_execute,
            'is_multi_request' => $request->is_multi_request,
            'is_split_service_req' => $request->is_split_service_req,
            'is_out_parent_fee' => $request->is_out_parent_fee,

            'is_allow_expend' => $request->is_allow_expend,
            'is_auto_expend' => $request->is_auto_expend,
            'is_out_of_drg' => $request->is_out_of_drg,
            'is_out_of_management' => $request->is_out_of_management,
            'is_other_source_paid' => $request->is_other_source_paid,
            'is_enable_assign_price' => $request->is_enable_assign_price,

            'is_not_show_tracking' => $request->is_not_show_tracking,
            'must_be_consulted' => $request->must_be_consulted,
            'is_block_department_tran' => $request->is_block_department_tran,
            'allow_simultaneity' => $request->allow_simultaneity,
            'is_not_required_complete' => $request->is_not_required_complete,
            'do_not_use_bhyt' => $request->do_not_use_bhyt,

            'allow_send_pacs' => $request->allow_send_pacs,
            'other_pay_source_id' => $request->other_pay_source_id,
            'attach_assign_print_type_code' => $request->attach_assign_print_type_code,
            'description' => $request->description,
            'notice' => $request->notice,
            'tax_rate_type' => $request->tax_rate_type,

            'process_code' => $request->process_code,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'service_code' => $request->service_code,
            'service_name' => $request->service_name,
            'service_unit_id' => $request->service_unit_id,
            'speciality_code' => $request->speciality_code,
            'hein_service_type_id' => $request->hein_service_type_id,

            'hein_service_bhyt_code' => $request->hein_service_bhyt_code,
            'hein_service_bhyt_name' => $request->hein_service_bhyt_name,
            'hein_order' => $request->hein_order,
            'parent_id' => $request->parent_id,
            'package_id' => $request->package_id,
            'package_price' => $request->package_price,

            'bill_option' => $request->bill_option,
            'bill_patient_type_id' => $request->bill_patient_type_id,
            'pttt_method_id' => $request->pttt_method_id,
            'is_not_change_bill_paty' => $request->is_not_change_bill_paty,
            'applied_patient_classify_ids' => $request->applied_patient_classify_ids,
            'applied_patient_type_ids' => $request->applied_patient_type_ids,

            'testing_technique' => $request->testing_technique,
            'default_patient_type_id' => $request->default_patient_type_id,
            'pttt_group_id' => $request->pttt_group_id,
            'hein_limit_price_old' => $request->hein_limit_price_old,
            'icd_cm_id' => $request->icd_cm_id,
            'hein_limit_price_in_time' => $request->hein_limit_price_in_time,

            'hein_limit_price' => $request->hein_limit_price,
            'cogs' => $request->cogs,
            'ration_symbol' => $request->ration_symbol,
            'ration_group_id' => $request->ration_group_id,
            'num_order' => $request->num_order,
            'pacs_type_code' => $request->pacs_type_code,

            'diim_type_id' => $request->diim_type_id,
            'fuex_type_id' => $request->fuex_type_id,
            'test_type_id' => $request->test_type_id,
            'sample_type_code' => $request->sample_type_code,
            'max_expend' => $request->max_expend,
            'number_of_film' => $request->number_of_film,

            'film_size_id' => $request->film_size_id,
            'min_process_time' => $request->min_process_time,
            'min_proc_time_except_paty_ids' => $request->min_proc_time_except_paty_ids,
            'estimate_duration' => $request->estimate_duration,
            'max_process_time' => $request->max_process_time,
            'max_proc_time_except_paty_ids' => $request->max_proc_time_except_paty_ids,

            'age_from' => $request->age_from,
            'age_to' => $request->age_to,
            'max_total_process_time' => $request->max_total_process_time,
            'total_time_except_paty_ids' => $request->total_time_except_paty_ids,
            'gender_id' => $request->gender_id,
            'min_duration' => $request->min_duration,

            'max_amount' => $request->max_amount,
            'body_part_ids' => $request->body_part_ids,
            'capacity' => $request->capacity,
            'warning_sampling_time' => $request->warning_sampling_time,
            'exe_service_module_id' => $request->exe_service_module_id,
            'suim_index_id' => $request->suim_index_id,

            'is_kidney' => $request->is_kidney,
            'is_antibiotic_resistance' => $request->is_antibiotic_resistance,
            'is_disallowance_no_execute' => $request->is_disallowance_no_execute,
            'is_multi_request' => $request->is_multi_request,
            'is_split_service_req' => $request->is_split_service_req,
            'is_out_parent_fee' => $request->is_out_parent_fee,

            'is_allow_expend' => $request->is_allow_expend,
            'is_auto_expend' => $request->is_auto_expend,
            'is_out_of_drg' => $request->is_out_of_drg,
            'is_out_of_management' => $request->is_out_of_management,
            'is_other_source_paid' => $request->is_other_source_paid,
            'is_enable_assign_price' => $request->is_enable_assign_price,

            'is_not_show_tracking' => $request->is_not_show_tracking,
            'must_be_consulted' => $request->must_be_consulted,
            'is_block_department_tran' => $request->is_block_department_tran,
            'allow_simultaneity' => $request->allow_simultaneity,
            'is_not_required_complete' => $request->is_not_required_complete,
            'do_not_use_bhyt' => $request->do_not_use_bhyt,

            'allow_send_pacs' => $request->allow_send_pacs,
            'other_pay_source_id' => $request->other_pay_source_id,
            'attach_assign_print_type_code' => $request->attach_assign_print_type_code,
            'description' => $request->description,
            'notice' => $request->notice,
            'tax_rate_type' => $request->tax_rate_type,

            'process_code' => $request->process_code,
            'is_active' => $request->is_active,
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_service.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service.id');
            $maxId = $this->applyJoins()->max('his_service.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service', 'his_service', $startId, $endId, $batchSize);
            }
        }
    }
}
