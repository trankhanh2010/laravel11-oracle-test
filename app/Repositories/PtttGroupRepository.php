<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PtttGroup;
use Illuminate\Support\Facades\DB;

class PtttGroupRepository
{
    protected $ptttGroup;
    public function __construct(PtttGroup $ptttGroup)
    {
        $this->ptttGroup = $ptttGroup;
    }

    public function applyJoins()
    {
        return $this->ptttGroup
            ->select(
                'his_pttt_group.*'
            );
    }
    public function applyWith($query){
        return $query->with($this->paramWith());
    }
    public function paramWith(){
        return [
           'bed_services:service_name,service_code'
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_group.pttt_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_pttt_group.pttt_group_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_group.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_pttt_group.' . $key, $item);
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
        return $this->ptttGroup->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $data = $this->ptttGroup::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'pttt_group_code' => $request->pttt_group_code,
            'pttt_group_name' => $request->pttt_group_name,
            'num_order' => $request->num_order,
            'remuneration' => $request->remuneration,
            'is_active' => $request->is_active,
        ]);
        if ($request->bed_service_type_ids !== null) {
            $dataToSync_bed_service_type_ids = [];
            $request->bed_service_type_ids = explode(',', $request->bed_service_type_ids);
            foreach ($request->bed_service_type_ids as $item) {
                $id = $item;
                $dataToSync_bed_service_type_ids[$id] = [];
                $dataToSync_bed_service_type_ids[$id]['create_time'] = now()->format('Ymdhis');
                $dataToSync_bed_service_type_ids[$id]['modify_time'] = now()->format('Ymdhis');
                $dataToSync_bed_service_type_ids[$id]['creator'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_bed_service_type_ids[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_bed_service_type_ids[$id]['app_creator'] = $appCreator;
                $dataToSync_bed_service_type_ids[$id]['app_modifier'] = $appModifier;
            }
            $data->bed_services()->sync($dataToSync_bed_service_type_ids);
        }
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'pttt_group_code' => $request->pttt_group_code,
            'pttt_group_name' => $request->pttt_group_name,
            'num_order' => $request->num_order,
            'remuneration' => $request->remuneration,
            'is_active' => $request->is_active,
        ];
        $data->fill($data_update);
        $data->save();
        if ($request->bed_service_type_ids !== null) {
            $dataToSync_bed_service_type_ids = [];
            $request->bed_service_type_ids = explode(',', $request->bed_service_type_ids);
            foreach ($request->bed_service_type_ids as $item) {
                $id = $item;
                $dataToSync_bed_service_type_ids[$id] = [];
                $dataToSync_bed_service_type_ids[$id]['modify_time'] = now()->format('Ymdhis');
                $dataToSync_bed_service_type_ids[$id]['modifier'] = get_loginname_with_token($request->bearerToken(), $time);
                $dataToSync_bed_service_type_ids[$id]['app_modifier'] = $appModifier;
            }
            $data->bed_services()->sync($dataToSync_bed_service_type_ids);
        }
        else{
            $data->bed_services()->sync([]);
        }
        DB::connection('oracle_his')->commit();
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
            $data = $this->applyJoins()->where('his_pttt_group.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_pttt_group.id');
            $maxId = $this->applyJoins()->max('his_pttt_group.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('pttt_group', 'his_pttt_group', $startId, $endId, $batchSize, $this->paramWith());
            }
        }
    }
}