<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PatientType;
use App\Models\HIS\ServicePaty;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ServicePatyRepository
{
    protected $servicePaty;
    protected $patientType;
    protected $patientTypeVienPhiId;

    public function __construct(
        ServicePaty $servicePaty,
    ) {
        $this->servicePaty = $servicePaty;
        $this->patientType = new PatientType();
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $cacheKey = 'patient_type_vien_phi_id';
        $this->patientTypeVienPhiId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data = $this->patientType->where('patient_type_code', '02')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
    }

    public function applyJoins()
    {
        return $this->servicePaty
            ->leftJoin('his_service as service', 'service.id', '=', 'his_service_paty.service_id')
            ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_service_paty.patient_type_id')
            ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_service_paty.branch_id')
            ->leftJoin('his_package as package', 'package.id', '=', 'his_service_paty.package_id')
            ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
            ->select(
                'his_service_paty.*',
                'service.service_name',
                'service.service_code',
                'service.service_type_id',
                'patient_type.patient_type_name',
                'patient_type.patient_type_code',
                'branch.branch_name',
                'branch.branch_code',
                'package.package_name',
                'package.package_code',
                'service_type.service_type_name',
                'service_type.service_type_code'
            );
    }
    public function applyJoinsGetData()
    {
        return $this->servicePaty
            ->select([
                'his_service_paty.service_id',
                'his_service_paty.patient_type_id',
                'his_service_paty.price',
                'his_service_paty.vat_ratio',
                'his_service_paty.treatment_from_time',
                'his_service_paty.treatment_to_time',
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('service.service_name'), 'like', $keyword . '%');
        });
    }
    public function applyServiceTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->where(function ($query) use ($ids) {
                // Khởi tạo biến cờ
                $isFirst = true;
                foreach ($ids as $key => $item) {
                    if ($isFirst) {
                        $query = $query->where(DB::connection('oracle_his')->raw('service_type_id'), $item);
                        $isFirst = false; // Đặt cờ thành false sau lần đầu tiên
                    } else {
                        $query = $query->orWhere(DB::connection('oracle_his')->raw('service_type_id'), $item);
                    }
                }
            });
        }
        return $query;
    }
    public function applyPatientTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->where(function ($query) use ($ids) {
                // Khởi tạo biến cờ
                $isFirst = true;
                foreach ($ids as $key => $item) {
                    if ($isFirst) {
                        $query = $query->where(DB::connection('oracle_his')->raw('patient_type_id'), $item);
                        $isFirst = false; // Đặt cờ thành false sau lần đầu tiên
                    } else {
                        $query = $query->orWhere(DB::connection('oracle_his')->raw('patient_type_id'), $item);
                    }
                }
            });
        }
        return $query;
    }
    public function applyServiceIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_paty.service_id'), $id);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        switch ($param) {
            case 'selectDTTT':
                $query->select([
                    'his_service_paty.patient_type_id',
                    'patient_type.patient_type_code',
                    'patient_type.patient_type_name',
                    'patient_type.is_addition_required',
                ])->distinct();
                return $query;
            case 'selectDTPT':
                $query->select([
                    'his_service_paty.patient_type_id',
                    'patient_type.patient_type_code',
                    'patient_type.patient_type_name',
                ])
                    ->where('patient_type.IS_ADDITION', 1)
                    ->distinct();
                return $query;
            default:
                return $query;
        }
    }
    public function applyPackageIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_paty.package_id'), $id);
        }
        return $query;
    }
    public function applyEffectiveFilter($query, $effective)
    {
        $now = now()->format('YmdHis');
        if ($effective) {
            $query->where(DB::connection('oracle_his')->raw('his_service_paty.to_time'), '>=', $now)
                ->orWhere(DB::connection('oracle_his')->raw('his_service_paty.to_time'), null);
        }
        return $query;
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_paty.is_active'), $isActive);
        }
        return $query;
    }

    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['service_name', 'service_code'])) {
                        $query->orderBy('service.' . $key, $item);
                    }
                    if (in_array($key, ['patient_type_name', 'patient_type_code'])) {
                        $query->orderBy('patient_type.' . $key, $item);
                    }
                    if (in_array($key, ['branch_name', 'branch_code'])) {
                        $query->orderBy('branch.' . $key, $item);
                    }
                    if (in_array($key, ['package_name', 'package_code'])) {
                        $query->orderBy('package.' . $key, $item);
                    }
                    if (in_array($key, ['service_type_name', 'service_type_code'])) {
                        $query->orderBy('service_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_paty.' . $key, $item);
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
        return $this->servicePaty->find($id);
    }
    public function getActivePriceByServieIdPatientTypeId($serviceId, $patientTypeId, $inTime)
    {
        $data = $this->servicePaty
            ->where('service_id', $serviceId)
            ->where('patient_type_id', $patientTypeId)
            // ->where(function ($q) {
            //     $q->whereNull('from_time')
            //         ->orWhere('from_time', '<=', now()->format('YmdHis'));
            // })
            // ->where(function ($q) {
            //     $q->whereNull('to_time')
            //         ->orWhere('to_time', '>=', now()->format('YmdHis'));
            // })
            ->where(function ($q) use ($inTime) {
                // Kiểm tra thời gian vào có lớn hơn bằng thời gian điều trị áp dụng từ không
                $q->whereNull('treatment_from_time')
                    ->orWhere('treatment_from_time', '<=', $inTime);
            })
            ->where(function ($q) use ($inTime) {
                // Kiểm tra thời gian vào có bé hơn bằng thời gian điều trị áp dụng đến không
                $q->whereNull('treatment_to_time')
                    ->orWhere('treatment_to_time', '>=', $inTime);
            })
            ->orderBy('from_time', 'desc')
            ->first();
        return $data;
    }
    public function getDonGiaVienPhi($serviceId, $inTime)
    {
        return $this->getActivePriceByServieIdPatientTypeId($serviceId, $this->patientTypeVienPhiId, $inTime);
    }
    public function create($request, $time, $appCreator, $appModifier, $branchId, $patientTypeId)
    {
        $data = $this->servicePaty::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'service_type_id' => $request->service_type_id,
            'service_id' => $request->service_id,
            'patient_type_id' => $patientTypeId,
            'branch_id' => $branchId,
            'patient_classify_id' => $request->patient_classify_id,
            'price' => $request->price,
            'vat_ratio' => $request->vat_ratio,
            'overtime_price' => $request->overtime_price,
            'actual_price' => $request->actual_price,
            'priority' => $request->priority,
            'ration_time_id' => $request->ration_time_id,
            'package_id' => $request->package_id,
            'service_condition_id' => $request->service_condition_id,
            'intruction_number_from' => $request->intruction_number_from,
            'intruction_number_to' => $request->intruction_number_to,
            'instr_num_by_type_from' => $request->instr_num_by_type_from,
            'instr_num_by_type_to' => $request->instr_num_by_type_to,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
            'treatment_from_time' => $request->treatment_from_time,
            'treatment_to_time' => $request->treatment_to_time,
            'day_from' => $request->day_from,
            'day_to' => $request->day_to,
            'hour_from' => $request->hour_from,
            'hour_to' => $request->hour_to,
            'execute_room_ids' => $request->execute_room_ids,
            'request_deparment_ids' => $request->request_deparment_ids,
            'request_room_ids' => $request->request_room_ids,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'patient_type_id' => $request->patient_type_id,
            'branch_id' => $request->branch_id,
            'patient_classify_id' => $request->patient_classify_id,
            'price' => $request->price,
            'vat_ratio' => $request->vat_ratio,
            'overtime_price' => $request->overtime_price,
            'actual_price' => $request->actual_price,
            'priority' => $request->priority,
            'ration_time_id' => $request->ration_time_id,
            'package_id' => $request->package_id,
            'service_condition_id' => $request->service_condition_id,
            'intruction_number_from' => $request->intruction_number_from,
            'intruction_number_to' => $request->intruction_number_to,
            'instr_num_by_type_from' => $request->instr_num_by_type_from,
            'instr_num_by_type_to' => $request->instr_num_by_type_to,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
            'treatment_from_time' => $request->treatment_from_time,
            'treatment_to_time' => $request->treatment_to_time,
            'day_from' => $request->day_from,
            'day_to' => $request->day_to,
            'hour_from' => $request->hour_from,
            'hour_to' => $request->hour_to,
            'execute_room_ids' => $request->execute_room_ids,
            'request_deparment_ids' => $request->request_deparment_ids,
            'request_room_ids' => $request->request_room_ids,
            'is_active' => $request->is_active
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
            $data = $this->applyJoins()->where('his_service_paty.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service_paty.id');
            $maxId = $this->applyJoins()->max('his_service_paty.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_paty', 'his_service_paty', $startId, $endId, $batchSize);
            }
        }
    }
}
