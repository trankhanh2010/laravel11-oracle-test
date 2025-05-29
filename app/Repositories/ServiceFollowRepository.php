<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceFollow;
use Illuminate\Support\Facades\DB;

class ServiceFollowRepository
{
    protected $serviceFollow;
    public function __construct(ServiceFollow $serviceFollow)
    {
        $this->serviceFollow = $serviceFollow;
    }

    public function applyJoins()
    {
        return $this->serviceFollow
        ->leftJoin('his_service as service', 'service.id', '=', 'his_service_follow.service_id')
        ->leftJoin('his_service as service_follow', 'service_follow.id', '=', 'his_service_follow.follow_id')

        ->select(
            'his_service_follow.*',
            'service.service_name',
            'service.service_code',
            'service_follow.service_name as service_follow_name',
            'service_follow.service_code as service_follow_code',

        );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('service.service_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_follow.is_active'), $isActive);
        }
        return $query;
    }
    public function applyServiceIdFilter($query, $serviceId)
    {
        if ($serviceId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_follow.service_id'), $serviceId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['service_name', 'service_code'])) {
                        $query->orderBy('service.' . $key, $item);
                    }
                    if (in_array($key, ['service_type_name', 'service_type_code'])) {
                        $query->orderBy('service_type.' . $key, $item);
                    }
                    if (in_array($key, ['service_follow_name', 'service_follow_code'])) {
                        $query->orderBy('service_follow.' . $key, $item);
                    }
                    if (in_array($key, ['service_follow_type_name', 'service_follow_type_code'])) {
                        $query->orderBy('service_follow_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_service_follow.' . $key, $item);
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
        return $this->serviceFollow->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->serviceFollow::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'service_id' => $request->service_id,
            'follow_id' => $request->follow_id,
            'amount' => $request->amount,
            'conditioned_amount' => $request->conditioned_amount,
            'treatment_type_ids' => $request->treatment_type_ids,
            'is_expend' => $request->is_expend,
            'add_if_not_assigned' => $request->add_if_not_assigned,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'service_id' => $request->service_id,
            'follow_id' => $request->follow_id,
            'amount' => $request->amount,
            'conditioned_amount' => $request->conditioned_amount,
            'is_expend' => $request->is_expend,
            'add_if_not_assigned' => $request->add_if_not_assigned,
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
            $data = $this->applyJoins()->where('his_service_follow.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_service_follow.id');
            $maxId = $this->applyJoins()->max('his_service_follow.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_follow', 'his_service_follow', $startId, $endId, $batchSize);
            }
        }
    }
}