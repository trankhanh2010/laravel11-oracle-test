<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\EmotionlessMethod;
use Illuminate\Support\Facades\DB;

class EmotionlessMethodRepository
{
    protected $emotionlessMethod;
    public function __construct(EmotionlessMethod $emotionlessMethod)
    {
        $this->emotionlessMethod = $emotionlessMethod;
    }

    public function applyJoins()
    {
        return $this->emotionlessMethod
            ->select(
                'his_emotionless_method.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_emotionless_method.emotionless_method_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_emotionless_method.emotionless_method_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_emotionless_method.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_emotionless_method.' . $key, $item);
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
        return $this->emotionlessMethod->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        // Nếu chọn cả 2 phương pháp thì để trống cả 2
        $is_first = $request->is_first;
        $is_second = $request->is_second;
        if(($request->is_first == 1) && ($request->is_second == 1)){
            $is_first = null;
            $is_second = null;
        }        
        $data = $this->emotionlessMethod::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'emotionless_method_code' => $request->emotionless_method_code,
            'emotionless_method_name' => $request->emotionless_method_name,
            'is_first' => $is_first,
            'is_second' => $is_second,
            'is_anaesthesia' => $request->is_anaesthesia,
            'hein_code' => $request->hein_code,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        // Nếu chọn cả 2 phương pháp thì để trống cả 2
        $is_first = $request->is_first;
        $is_second = $request->is_second;
        if(($request->is_first == 1) && ($request->is_second == 1)){
            $is_first = null;
            $is_second = null;
        }
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'emotionless_method_code' => $request->emotionless_method_code,
            'emotionless_method_name' => $request->emotionless_method_name,
            'is_first' => $is_first,
            'is_second' => $is_second,
            'is_anaesthesia' => $request->is_anaesthesia,
            'hein_code' => $request->hein_code,
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
            $data = $this->applyJoins()->where('his_emotionless_method.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_emotionless_method.id');
            $maxId = $this->applyJoins()->max('his_emotionless_method.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('emotionless_method', 'his_emotionless_method', $startId, $endId, $batchSize);
            }
        }
    }
}