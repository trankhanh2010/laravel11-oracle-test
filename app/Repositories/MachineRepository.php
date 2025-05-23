<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Machine;
use Illuminate\Support\Facades\DB;

class MachineRepository
{
    protected $machine;
    public function __construct(Machine $machine)
    {
        $this->machine = $machine;
    }

    public function applyJoins()
    {
        return $this->machine
            ->leftJoin('his_department as department', 'department.id', '=', 'his_machine.department_id')
            ->select(
                'his_machine.*',
                'department.department_code',
                'department.department_name'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_machine.machine_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_machine.machine_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_machine.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_machine.' . $key, $item);
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
        return $this->machine->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->machine::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            
            'machine_code' => $request->machine_code,
            'machine_name' => $request->machine_name,
            'serial_number' => $request->serial_number,
            'source_code' => $request->source_code,
            'machine_group_code' => $request->machine_group_code,
            'symbol' => $request->symbol,

            'manufacturer_name' => $request->manufacturer_name,
            'national_name' => $request->national_name,
            'manufactured_year' => $request->manufactured_year,
            'used_year' => $request->used_year,
            'circulation_number' => $request->circulation_number,
            'integrate_address' => $request->integrate_address,

            'max_service_per_day' => $request->max_service_per_day,
            'department_id' => $request->department_id,
            'room_ids' => $request->room_ids,
            'is_kidney' => $request->is_kidney,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'machine_code' => $request->machine_code,
            'machine_name' => $request->machine_name,
            'serial_number' => $request->serial_number,
            'source_code' => $request->source_code,
            'machine_group_code' => $request->machine_group_code,
            'symbol' => $request->symbol,

            'manufacturer_name' => $request->manufacturer_name,
            'national_name' => $request->national_name,
            'manufactured_year' => $request->manufactured_year,
            'used_year' => $request->used_year,
            'circulation_number' => $request->circulation_number,
            'integrate_address' => $request->integrate_address,

            'max_service_per_day' => $request->max_service_per_day,
            'department_id' => $request->department_id,
            'room_ids' => $request->room_ids,
            'is_kidney' => $request->is_kidney,

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
            $data = $this->applyJoins()->where('his_machine.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_machine.id');
            $maxId = $this->applyJoins()->max('his_machine.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('machine', 'his_machine', $startId, $endId, $batchSize);
            }
        }
    }
}