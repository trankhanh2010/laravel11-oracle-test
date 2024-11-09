<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\SuimIndexUnit;
use Illuminate\Support\Facades\DB;

class SuimIndexUnitRepository
{
    protected $suimIndexUnit;
    public function __construct(SuimIndexUnit $suimIndexUnit)
    {
        $this->suimIndexUnit = $suimIndexUnit;
    }

    public function applyJoins()
    {
        return $this->suimIndexUnit
            ->select(
                'his_suim_index_unit.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_suim_index_unit.suim_index_unit_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_suim_index_unit.suim_index_unit_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_suim_index_unit.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_suim_index_unit.' . $key, $item);
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
        return $this->suimIndexUnit->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->suimIndexUnit::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'suim_index_unit_code' => $request->suim_index_unit_code,
            'suim_index_unit_name' => $request->suim_index_unit_name,
            'suim_index_unit_symbol' => $request->suim_index_unit_symbol,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'suim_index_unit_code' => $request->suim_index_unit_code,
            'suim_index_unit_name' => $request->suim_index_unit_name,
            'suim_index_unit_symbol' => $request->suim_index_unit_symbol,
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
            $data = $this->applyJoins()->where('his_suim_index_unit.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_suim_index_unit.id');
            $maxId = $this->applyJoins()->max('his_suim_index_unit.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('suim_index_unit', 'his_suim_index_unit', $startId, $endId, $batchSize);
            }
        }
    }
}