<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\TranPatiTemp;
use Illuminate\Support\Facades\DB;

class TranPatiTempRepository
{
    protected $tranPatiTemp;
    public function __construct(TranPatiTemp $tranPatiTemp)
    {
        $this->tranPatiTemp = $tranPatiTemp;
    }

    public function applyJoins()
    {
        return $this->tranPatiTemp
            ->select(
                'his_tran_pati_temp.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_tran_pati_temp.tran_pati_temp_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_tran_pati_temp.tran_pati_temp_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_tran_pati_temp.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_tran_pati_temp.' . $key, $item);
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
        return $this->tranPatiTemp->find($id);
    }

    public function applySelectByLoginnameFilter($query, $currentLoginname)
    {
        if ($currentLoginname != null) {
            $query->where(function ($q) use ($currentLoginname) {
                $q->where(DB::connection('oracle_his')->raw('his_tran_pati_temp.creator'), $currentLoginname)
                ->orWhere(DB::connection('oracle_his')->raw('his_tran_pati_temp.is_public'), 1);
            });
        }
        return $query;
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->tranPatiTemp::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'tran_pati_temp_code' => $request->tran_pati_temp_code,
            'tran_pati_temp_name' => $request->tran_pati_temp_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'tran_pati_temp_code' => $request->tran_pati_temp_code,
            'tran_pati_temp_name' => $request->tran_pati_temp_name,
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
            $data = $this->applyJoins()->where('his_tran_pati_temp.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_tran_pati_temp.id');
            $maxId = $this->applyJoins()->max('his_tran_pati_temp.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('tran_pati_temp', 'his_tran_pati_temp', $startId, $endId, $batchSize);
            }
        }
    }
}