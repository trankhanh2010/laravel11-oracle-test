<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MaterialPaty;
use Illuminate\Support\Facades\DB;

class MaterialPatyRepository
{
    protected $materialPaty;
    public function __construct(MaterialPaty $materialPaty)
    {
        $this->materialPaty = $materialPaty;
    }

    public function applyJoins()
    {
        return $this->materialPaty
            ->select(
                'his_material_paty.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_material_paty.material_paty_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_material_paty.material_paty_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_material_paty.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_material_paty.' . $key, $item);
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
        return $this->materialPaty->find($id);
    }
    public function getActivePriceByMaterialIdPatientTypeId($materialId, $patientTypeId)
    {
        $data = $this->materialPaty
            ->where('material_id', $materialId)
            ->where('patient_type_id', $patientTypeId)
            ->orderBy('modify_time', 'desc')
            ->first();
        return $data?$data->exp_price*(1+$data->exp_vat_ratio):null;
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->materialPaty::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'material_paty_code' => $request->material_paty_code,
    //         'material_paty_name' => $request->material_paty_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'material_paty_code' => $request->material_paty_code,
    //         'material_paty_name' => $request->material_paty_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    // public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    // {
    //     $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
    //     if ($id != null) {
    //         $data = $this->applyJoins()->where('his_material_paty.id', '=', $id)->first();
    //         if ($data) {
    //             $data = $data->getAttributes();
    //             return $data;
    //         }
    //     } else {
    //         // Xác định min và max id
    //         $minId = $this->applyJoins()->min('his_material_paty.id');
    //         $maxId = $this->applyJoins()->max('his_material_paty.id');
    //         $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
    //         for ($i = 0; $i < $numJobs; $i++) {
    //             $startId = $minId + ($i * $chunkSize);
    //             $endId = $startId + $chunkSize - 1;
    //             // Đảm bảo chunk cuối cùng bao phủ đến maxId
    //             if ($i == $numJobs - 1) {
    //                 $endId = $maxId;
    //             }
    //             // Dispatch job cho mỗi phạm vi id
    //             ProcessElasticIndexingJob::dispatch('material_paty', 'his_material_paty', $startId, $endId, $batchSize);
    //         }
    //     }
    // }
}