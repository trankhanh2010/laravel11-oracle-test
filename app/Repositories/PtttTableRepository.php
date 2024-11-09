<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PtttTable;
use Illuminate\Support\Facades\DB;

class PtttTableRepository
{
    protected $ptttTable;
    public function __construct(PtttTable $ptttTable)
    {
        $this->ptttTable = $ptttTable;
    }

    public function applyJoins()
    {
        return $this->ptttTable
        ->leftJoin('his_execute_room as execute_room', 'execute_room.id', '=', 'his_pttt_table.execute_room_id')
        ->leftJoin('his_room as room', 'room.id', '=', 'execute_room.room_id')
        ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')
        ->leftJoin('his_area as area', 'area.id', '=', 'room.area_id')
            ->select(
                'his_pttt_table.*',
                'execute_room.execute_room_code',
                'execute_room.execute_room_name',
                'execute_room.max_request_by_day',
                'department.department_code',
                'department.department_name',
                'area.area_code',
                'area.area_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_table.pttt_table_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_pttt_table.pttt_table_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_pttt_table.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['execute_room_code', 'execute_room_name', 'max_request_by_day'])) {
                        $query->orderBy('execute_room.' . $key, $item);
                    }
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['area_code', 'area_name'])) {
                        $query->orderBy('area.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_pttt_table.' . $key, $item);
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
        return $this->ptttTable->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->ptttTable::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'pttt_table_code' => $request->pttt_table_code,
            'pttt_table_name' => $request->pttt_table_name,
            'execute_room_id' => $request->execute_room_id,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'pttt_table_name' => $request->pttt_table_name,
            'execute_room_id' => $request->execute_room_id,
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
            $data = $this->applyJoins()->where('his_pttt_table.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_pttt_table.id');
            $maxId = $this->applyJoins()->max('his_pttt_table.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('pttt_table', 'his_pttt_table', $startId, $endId, $batchSize);
            }
        }
    }
}