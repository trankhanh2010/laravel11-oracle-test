<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\SaleProfitCfg;
use Illuminate\Support\Facades\DB;

class SaleProfitCfgRepository
{
    protected $saleProfitCfg;
    public function __construct(SaleProfitCfg $saleProfitCfg)
    {
        $this->saleProfitCfg = $saleProfitCfg;
    }

    public function applyJoins()
    {
        return $this->saleProfitCfg
            ->select(
                'his_sale_profit_cfg.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_sale_profit_cfg.imp_price_from'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sale_profit_cfg.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_sale_profit_cfg.' . $key, $item);
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
        return $this->saleProfitCfg->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->saleProfitCfg::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'ratio' => $request->ratio,
            'imp_price_from' => $request->imp_price_from,
            'imp_price_to' => $request->imp_price_to,
            'is_medicine' => $request->is_medicine,
            'is_material' => $request->is_material,
            'is_common_medicine' => $request->is_common_medicine,
            'is_functional_food' => $request->is_functional_food,
            'is_drug_store' => $request->is_drug_store,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'ratio' => $request->ratio,
            'imp_price_from' => $request->imp_price_from,
            'imp_price_to' => $request->imp_price_to,
            'is_medicine' => $request->is_medicine,
            'is_material' => $request->is_material,
            'is_common_medicine' => $request->is_common_medicine,
            'is_functional_food' => $request->is_functional_food,
            'is_drug_store' => $request->is_drug_store,
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
            $data = $this->applyJoins()->where('his_sale_profit_cfg.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_sale_profit_cfg.id');
            $maxId = $this->applyJoins()->max('his_sale_profit_cfg.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sale_profit_cfg', 'his_sale_profit_cfg', $startId, $endId, $batchSize);
            }
        }
    }
}