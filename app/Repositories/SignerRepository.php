<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\EMR\Signer;
use Illuminate\Support\Facades\DB;

class SignerRepository
{
    protected $signer;
    public function __construct(Signer $signer)
    {
        $this->signer = $signer;
    }

    public function applyJoins($tab = null)
    {
        if($tab == 'selectBacSi')
        return $this->signer->select([
            'id',
            'loginname',
            'username',
            'title',
            // 'sign_image'
        ]);
        return $this->signer
            ->select([
                'id',
                'is_active',
                'is_delete',
                'loginname',
                'username',
                'title',
                'department_code',
                'department_name',
            ]
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_emr')->raw('emr_signer.loginame'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_emr')->raw('emr_signer.username'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_emr')->raw('emr_signer.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_emr')->raw('emr_signer.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        if ($param != null) {
            switch ($param) {
                case 'selectBacSi':
                    $query->where(DB::connection('oracle_his')->raw('title'), 'Bác Sĩ');
                    return $query;
                default:
                    return $query;
            }
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('emr_signer.' . $key, $item);
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
        return $this->signer->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->signer::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'signer_code' => $request->signer_code,
            'signer_name' => $request->signer_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'signer_code' => $request->signer_code,
            'signer_name' => $request->signer_name,
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
            $data = $this->applyJoins()->where('emr_signer.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('emr_signer.id');
            $maxId = $this->applyJoins()->max('emr_signer.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('signer', 'emr_signer', $startId, $endId, $batchSize);
            }
        }
    }
}