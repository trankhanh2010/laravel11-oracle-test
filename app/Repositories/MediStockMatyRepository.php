<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\MediStockMaty;
use Illuminate\Support\Facades\DB;

class MediStockMatyRepository
{
    protected $mediStockMaty;
    public function __construct(MediStockMaty $mediStockMaty)
    {
        $this->mediStockMaty = $mediStockMaty;
    }

    public function applyJoins()
    {
        return $this->mediStockMaty
            ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_maty.medi_stock_id')
            ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_medi_stock_maty.material_type_id')
            ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'material_type.tdl_service_unit_id')
            ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_maty.exp_medi_stock_id')

            ->select(
                'his_medi_stock_maty.*',
                'medi_stock.medi_stock_code',
                'medi_stock.medi_stock_name',
                'service_unit.service_unit_code',
                'service_unit.service_unit_name',
                'material_type.material_type_code',
                'material_type.material_type_name',
                'exp_medi_stock.medi_stock_code as exp_medi_stock_code',
                'exp_medi_stock.medi_stock_name as exp_medi_stock_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query
                ->where(DB::connection('oracle_his')->raw('medi_stock.medi_stock_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('medi_stock.medi_stock_name'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('material_type.material_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('material_type.material_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.is_active'), $isActive);
        }
        return $query;
    }
    public function applyMediStockIdFilter($query, $mediStockId)
    {
        if ($mediStockId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.medi_stock_id'), $mediStockId);
        }
        return $query;
    }
    public function applyMaterialTypeIdFilter($query, $materialTypeId)
    {
        if ($materialTypeId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock_maty.material_type_id'), $materialTypeId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['medi_stock_code', 'medi_stock_name'])) {
                        $query->orderBy('medi_stock.' . $key, $item);
                    }
                    if (in_array($key, ['material_type_code', 'material_type_name'])) {
                        $query->orderBy('material_type.' . $key, $item);
                    }
                    if (in_array($key, ['service_unit_code', 'service_unit_name'])) {
                        $query->orderBy('service_unit.' . $key, $item);
                    }
                    if (in_array($key, ['exp_medi_stock_code', 'exp_medi_stock_name'])) {
                        $query->orderBy('exp_medi_stock.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_medi_stock_maty.' . $key, $item);
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
        return $this->mediStockMaty->find($id);
    }
    public function getByMediStockIdAndMaterialTypeIds($mediStockId, $materialTypeIds)
    {
        return $this->mediStockMaty->where('medi_stock_id', $mediStockId)->whereIn('material_type_id', $materialTypeIds)->get();
    }
    public function getByMaterialTypeIdAndMediStockIds($materialTypeId, $mediStockIds)
    {
        return $this->mediStockMaty->whereIn('medi_stock_id', $mediStockIds)->where('material_type_id', $materialTypeId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByMediStockId($id)
    {
        $ids = $this->mediStockMaty->where('medi_stock_id', $id)->pluck('id')->toArray();
        $this->mediStockMaty->where('medi_stock_id', $id)->delete();
        return $ids;
    }
    public function deleteByMaterialTypeId($id)
    {
        $ids = $this->mediStockMaty->where('material_type_id', $id)->pluck('id')->toArray();
        $this->mediStockMaty->where('material_type_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_medi_stock_maty.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_medi_stock_maty.id');
            $maxId = $this->applyJoins()->max('his_medi_stock_maty.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('medi_stock_maty', 'his_medi_stock_maty', $startId, $endId, $batchSize);
            }
        }
    }
}
