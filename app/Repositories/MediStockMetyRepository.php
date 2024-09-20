<?php

namespace App\Repositories;

use App\Models\HIS\MediStockMety;
use Illuminate\Support\Facades\DB;

class MediStockMetyRepository
{
    protected $mediStockMety;
    public function __construct(MediStockMety $mediStockMety)
    {
        $this->mediStockMety = $mediStockMety;
    }

    public function applyJoins()
    {
        return $this->mediStockMety
            ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_medi_stock_mety.medi_stock_id')
            ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medi_stock_mety.medicine_type_id')
            ->leftJoin('his_service_unit as service_unit', 'service_unit.id', '=', 'medicine_type.tdl_service_unit_id')
            ->leftJoin('his_medi_stock as exp_medi_stock', 'exp_medi_stock.id', '=', 'his_medi_stock_mety.exp_medi_stock_id')

            ->select(
                'his_medi_stock_mety.*',
                'medi_stock.medi_stock_code',
                'medi_stock.medi_stock_name',
                'service_unit.service_unit_code',
                'service_unit.service_unit_name',
                'medicine_type.medicine_type_code',
                'medicine_type.medicine_type_name',
                'medicine_type.concentra',
                'medicine_type.register_number',
                'medicine_type.active_ingr_bhyt_code',
                'medicine_type.active_ingr_bhyt_name',
                'medicine_type.distributed_amount',
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
                ->orWhere(DB::connection('oracle_his')->raw('medicine_type.medicine_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('medicine_type.medicine_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.is_active'), $isActive);
        }
        return $query;
    }
    public function applyMediStockIdFilter($query, $mediStockId)
    {
        if ($mediStockId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.medi_stock_id'), $mediStockId);
        }
        return $query;
    }
    public function applyMedicineTypeIdFilter($query, $medicineTypeId)
    {
        if ($medicineTypeId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medi_stock_mety.medicine_type_id'), $medicineTypeId);
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
                    if (in_array($key, [
                        'medicine_type_code', 
                        'medicine_type_name', 
                        'distributed_amount', 
                        'active_ingr_bhyt_name', 
                        'active_ingr_bhyt_code',
                        'register_number',
                        'concentra'
                        ])) {
                        $query->orderBy('medicine_type.' . $key, $item);
                    }
                    if (in_array($key, ['service_unit_code', 'service_unit_name'])) {
                        $query->orderBy('service_unit.' . $key, $item);
                    }
                    if (in_array($key, ['exp_medi_stock_code', 'exp_medi_stock_name'])) {
                        $query->orderBy('exp_medi_stock.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_medi_stock_mety.' . $key, $item);
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
        return $this->mediStockMety->find($id);
    }
    public function getByMediStockIdAndMedicineTypeIds($mediStockId, $medicineTypeIds)
    {
        return $this->mediStockMety->where('medi_stock_id', $mediStockId)->whereIn('medicine_type_id', $medicineTypeIds)->get();
    }
    public function getByMedicineTypeIdAndMediStockIds($medicineTypeId, $mediStockIds)
    {
        return $this->mediStockMety->whereIn('medi_stock_id', $mediStockIds)->where('medicine_type_id', $medicineTypeId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByMediStockId($id)
    {
        $ids = $this->mediStockMety->where('medi_stock_id', $id)->pluck('id')->toArray();
        $this->mediStockMety->where('medi_stock_id', $id)->delete();
        return $ids;
    }
    public function deleteByMedicineTypeId($id)
    {
        $ids = $this->mediStockMety->where('medicine_type_id', $id)->pluck('id')->toArray();
        $this->mediStockMety->where('medicine_type_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_medi_stock_mety.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes();
            })->toArray();
        }
        return $data;
    }
}
