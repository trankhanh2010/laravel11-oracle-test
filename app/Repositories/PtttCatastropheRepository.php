<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PtttCatastrophe;
use Illuminate\Support\Facades\DB;

class PtttCatastropheRepository
{
    protected $ptttCatastrophe;
    public function __construct(PtttCatastrophe $ptttCatastrophe)
    {
        $this->ptttCatastrophe = $ptttCatastrophe;
    }

    public function applyJoins()
    {
        return $this->ptttCatastrophe
            ->select(
                'his_pttt_catastrophe.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_catastrophe.pttt_catastrophe_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_pttt_catastrophe.pttt_catastrophe_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_catastrophe.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_pttt_catastrophe.' . $key, $item);
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
        return $this->ptttCatastrophe->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->ptttCatastrophe::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'pttt_catastrophe_code' => $request->pttt_catastrophe_code,
            'pttt_catastrophe_name' => $request->pttt_catastrophe_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'pttt_catastrophe_code' => $request->pttt_catastrophe_code,
            'pttt_catastrophe_name' => $request->pttt_catastrophe_name,
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
            $data = $this->applyJoins()->where('his_pttt_catastrophe.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_pttt_catastrophe.id');
            $maxId = $this->applyJoins()->max('his_pttt_catastrophe.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('pttt_catastrophe', 'his_pttt_catastrophe', $startId, $endId, $batchSize);
            }
        }
    }
}