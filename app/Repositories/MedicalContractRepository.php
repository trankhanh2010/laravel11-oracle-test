<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MedicalContract;
use Illuminate\Support\Facades\DB;

class MedicalContractRepository
{
    protected $medicalContract;
    public function __construct(MedicalContract $medicalContract)
    {
        $this->medicalContract = $medicalContract;
    }

    public function applyJoins()
    {
        return $this->medicalContract
            ->select(
                'his_medical_contract.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medical_contract.medical_contract_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medical_contract.medical_contract_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medical_contract.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_medical_contract.' . $key, $item);
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
        return $this->medicalContract->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->medicalContract::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'medical_contract_code' => $request->medical_contract_code,
            'medical_contract_name' => $request->medical_contract_name,
            'supplier_id'  => $request->supplier_id,  
            'document_supplier_id' => $request->document_supplier_id, 
            'bid_id' => $request->bid_id,    
            'venture_agreening'  => $request->venture_agreening,
            'valid_from_date' => $request->valid_from_date,     
            'valid_to_date'  => $request->valid_to_date,  
            'note'  => $request->note,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'medical_contract_code' => $request->medical_contract_code,
            'medical_contract_name' => $request->medical_contract_name,
            'supplier_id'  => $request->supplier_id,  
            'document_supplier_id' => $request->document_supplier_id, 
            'bid_id' => $request->bid_id,    
            'venture_agreening'  => $request->venture_agreening,
            'valid_from_date' => $request->valid_from_date,     
            'valid_to_date'  => $request->valid_to_date,  
            'note'  => $request->note,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_medical_contract.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_medical_contract.id');
            $maxId = $this->applyJoins()->max('his_medical_contract.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('medical_contract', 'his_medical_contract', $startId, $endId, $batchSize);
            }
        }
    }
}