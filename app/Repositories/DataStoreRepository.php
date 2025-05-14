<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\DataStore;
use App\Models\HIS\Room;
use Illuminate\Support\Facades\DB;

class DataStoreRepository
{
    protected $dataStore;
    protected $room;
    public function __construct(DataStore $dataStore, Room $room)
    {
        $this->dataStore = $dataStore;
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->dataStore
            ->leftJoin('his_room as room', 'room.id', '=', 'his_data_store.room_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
            ->leftJoin('his_room as stored_room', 'stored_room.id', '=', 'his_data_store.stored_room_id')
            ->leftJoin('his_department as stored_department', 'stored_department.id', '=', 'stored_room.department_id')
            ->leftJoin('his_data_store as parent', 'parent.id', '=', 'his_data_store.parent_id')

            ->select(
                'his_data_store.*',
                'department.department_name',
                'department.department_code',
                'stored_department.department_name as stored_department_name',
                'stored_department.department_code as stored_department_code',
                'parent.data_store_code as parent_data_store_code',
                'parent.data_store_name as parent_data_store_name'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_data_store.data_store_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_data_store.data_store_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_data_store.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['department_name', 'department_code'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['stored_department_name', 'stored_department_code'])) {
                        $query->orderBy('stored_department.' . $key, $item);
                    }
                    if (in_array($key, ['parent_data_store_name', 'parent_data_store_code'])) {
                        $query->orderBy('parent.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_data_store.' . $key, $item);
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
        return $this->dataStore->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
       // Start transaction
       DB::connection('oracle_his')->beginTransaction();
       $room = $this->room::create([
           'create_time' => now()->format('YmdHis'),
           'modify_time' => now()->format('YmdHis'),
           'creator' => get_loginname_with_token($request->bearerToken(), $time),
           'modifier' => get_loginname_with_token($request->bearerToken(), $time),
           'app_creator' => $appCreator,
           'app_modifier' => $appModifier,
           'department_id' => $request->department_id,
           'room_type_id' => $request->room_type_id
       ]);
       $data = $this->dataStore::create([
           'create_time' => now()->format('YmdHis'),
           'modify_time' => now()->format('YmdHis'),
           'creator' => get_loginname_with_token($request->bearerToken(), $time),
           'modifier' => get_loginname_with_token($request->bearerToken(), $time),
           'app_creator' => $appCreator,
           'app_modifier' => $appModifier,
           'data_store_code' => $request->data_store_code,
           'data_store_name' => $request->data_store_name,
           'parent_id' => $request->parent_id,
           'stored_department_id' => $request->stored_department_id,
           'stored_room_id' => $request->stored_room_id,
           'treatment_end_type_ids' => $request->treatment_end_type_ids,
           'treatment_type_ids' => $request->treatment_type_ids,
           'room_id' => $room->id,
       ]);
       DB::connection('oracle_his')->commit();
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        // Start transaction
        DB::connection('oracle_his')->beginTransaction();
        $room_update = [
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'room_type_id' => $request->room_type_id,
            'is_active' => $request->is_active,

        ];
        $data_update = [
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'data_store_name' => $request->data_store_name,
            'parent_id' => $request->parent_id,
            'stored_department_id' => $request->stored_department_id,
            'stored_room_id' => $request->stored_room_id,
            'treatment_end_type_ids' => $request->treatment_end_type_ids,
            'treatment_type_ids' => $request->treatment_type_ids,
            'is_active' => $request->is_active,

        ];
        $room = $this->room->find($data->room_id);
        $room->fill($room_update);
        $room->save();
        $data->fill($data_update);
        $data->save();
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function delete($data)
    {
        DB::connection('oracle_his')->beginTransaction();
        $data->delete();
        $room = $this->room->find($data->room_id);
        $room->delete();
        DB::connection('oracle_his')->commit();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_data_store.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_data_store.id');
            $maxId = $this->applyJoins()->max('his_data_store.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('data_store', 'his_data_store', $startId, $endId, $batchSize);
            }
        }
    }
}