<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\LocationStore;
use Illuminate\Support\Facades\DB;

class LocationStoreRepository
{
    protected $locationStore;
    public function __construct(LocationStore $locationStore)
    {
        $this->locationStore = $locationStore;
    }

    public function applyJoins()
    {
        return $this->locationStore
        ->leftJoin('his_data_store as data_store', 'data_store.id', '=', 'his_location_store.data_store_id')
        ->select(
            'his_location_store.*',
            'data_store.data_store_code',
            'data_store.data_store_name',
        );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_location_store.location_store_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_location_store.location_store_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_location_store.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['data_store_code', 'data_store_name'])) {
                        $query->orderBy('data_store.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_location_store.' . $key, $item);
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
        return $this->locationStore->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->locationStore::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'location_store_code' => $request->location_store_code,
            'location_store_name' => $request->location_store_name,
            'data_store_id' => $request->data_store_id,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'location_store_name' => $request->location_store_name,
            'data_store_id' => $request->data_store_id,
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
            $data = $this->applyJoins()->where('his_location_store.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_location_store.id');
            $maxId = $this->applyJoins()->max('his_location_store.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('location_store', 'his_location_store', $startId, $endId, $batchSize);
            }
        }
    }
}