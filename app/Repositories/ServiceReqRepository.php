<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceReq;
use Illuminate\Support\Facades\DB;

class ServiceReqRepository
{
    protected $serviceReq;
    public function __construct(ServiceReq $serviceReq)
    {
        $this->serviceReq = $serviceReq;
    }

    public function applyJoins()
    {
        return $this->serviceReq
            ->select(
                'his_service_req.ID',
                'his_service_req.CREATE_TIME',
                'his_service_req.MODIFY_TIME',
                'his_service_req.CREATOR',
                'his_service_req.MODIFIER',
                'his_service_req.APP_CREATOR',
                'his_service_req.APP_MODIFIER',
                'his_service_req.IS_ACTIVE',
                'his_service_req.IS_DELETE',
                'his_service_req.SERVICE_REQ_CODE',
                'his_service_req.SERVICE_REQ_TYPE_ID',
                'his_service_req.SERVICE_REQ_STT_ID',
                'his_service_req.TREATMENT_ID',
                'his_service_req.INTRUCTION_TIME',
                'his_service_req.INTRUCTION_DATE',
                'his_service_req.REQUEST_ROOM_ID',
                'his_service_req.REQUEST_DEPARTMENT_ID',
                'his_service_req.REQUEST_LOGINNAME',
                'his_service_req.REQUEST_USERNAME',
                'his_service_req.EXECUTE_ROOM_ID',
                'his_service_req.EXECUTE_DEPARTMENT_ID',
                'his_service_req.EXECUTE_LOGINNAME',
                'his_service_req.EXECUTE_USERNAME',
                'his_service_req.START_TIME',
                'his_service_req.FINISH_TIME',
                'his_service_req.ICD_CODE',
                'his_service_req.ICD_NAME',
                'his_service_req.NUM_ORDER',
                'his_service_req.PRIORITY',
                'his_service_req.TRACKING_ID',
                'his_service_req.TREATMENT_TYPE_ID',
                'his_service_req.SESSION_CODE',
                'his_service_req.TDL_TREATMENT_CODE',
                'his_service_req.TDL_HEIN_CARD_NUMBER',
                'his_service_req.TDL_PATIENT_ID',
                'his_service_req.TDL_PATIENT_CODE',
                'his_service_req.TDL_PATIENT_NAME',
                'his_service_req.TDL_PATIENT_FIRST_NAME',
                'his_service_req.TDL_PATIENT_LAST_NAME',
                'his_service_req.TDL_PATIENT_DOB',
                'his_service_req.TDL_PATIENT_IS_HAS_NOT_DAY_DOB',
                'his_service_req.TDL_PATIENT_ADDRESS',
                'his_service_req.TDL_PATIENT_GENDER_ID',
                'his_service_req.TDL_PATIENT_GENDER_NAME',
                'his_service_req.TDL_PATIENT_NATIONAL_NAME',
                'his_service_req.TDL_HEIN_MEDI_ORG_CODE',
                'his_service_req.TDL_HEIN_MEDI_ORG_NAME',
                'his_service_req.TDL_TREATMENT_TYPE_ID',
                'his_service_req.VIR_KIDNEY',
                'his_service_req.TDL_PATIENT_TYPE_ID',
                'his_service_req.IS_SEND_BARCODE_TO_LIS',
                'his_service_req.CONSULTANT_LOGINNAME',
                'his_service_req.CONSULTANT_USERNAME',
                'his_service_req.IS_NOT_IN_DEBT',
                'his_service_req.VIR_INTRUCTION_MONTH',
                'his_service_req.BARCODE_LENGTH',
                'his_service_req.TDL_SERVICE_IDS',
                'his_service_req.TDL_PATIENT_NATIONAL_CODE',
                'his_service_req.TDL_PATIENT_UNSIGNED_NAME',
                'his_service_req.VIR_CREATE_DATE',
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'bed_logs',
            'drug_interventions',
            'exam_sere_dires',
            'exp_mests',
            'ksk_drivers',
            'his_ksk_driver_cars',
            'ksk_generals',
            'ksk_occupationals',
            'ksk_others',
            'ksk_over_eighteens',
            'ksk_period_drivers',
            'ksk_under_eighteens',
            'sere_servs',
            'sere_serv_exts',
            'sere_serv_rations',
            'service_req_matys',
            'service_req_metys'
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTreatmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_req.treatment_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_service_req.' . $key, $item);
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
        return $this->serviceReq->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->serviceReq::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'service_req_code' => $request->service_req_code,
    //         'service_req_name' => $request->service_req_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'service_req_code' => $request->service_req_code,
    //         'service_req_name' => $request->service_req_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_service_req.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service_req.id');
            $maxId = $this->applyJoins()->max('his_service_req.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_req', 'his_service_req', $startId, $endId, $batchSize);
            }
        }
    }
}