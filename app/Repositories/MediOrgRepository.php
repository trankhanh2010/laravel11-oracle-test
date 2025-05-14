<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MediOrg;
use Illuminate\Support\Facades\DB;

class MediOrgRepository
{
    protected $mediOrg;
    public function __construct(MediOrg $mediOrg)
    {
        $this->mediOrg = $mediOrg;
    }

    public function applyJoins()
    {
        return $this->mediOrg
            ->select(
                'his_medi_org.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_org.medi_org_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medi_org.medi_org_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_org.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_medi_org.' . $key, $item);
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
        return $this->mediOrg->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->mediOrg::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'medi_org_code' => $request->medi_org_code,
            'medi_org_name' => $request->medi_org_name,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'address' => $request->address,
            'rank_code' => $request->rank_code,
            'level_code' => $request->level_code,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'medi_org_code' => $request->medi_org_code,
            'medi_org_name' => $request->medi_org_name,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'address' => $request->address,
            'rank_code' => $request->rank_code,
            'level_code' => $request->level_code,
            'is_active' => $request->is_active,
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
            $data = $this->applyJoins()->where('his_medi_org.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_medi_org.id');
            $maxId = $this->applyJoins()->max('his_medi_org.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('medi_org', 'his_medi_org', $startId, $endId, $batchSize);
            }
        }
    }
}