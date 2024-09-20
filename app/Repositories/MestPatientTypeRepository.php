<?php

namespace App\Repositories;

use App\Models\HIS\MestPatientType;
use Illuminate\Support\Facades\DB;

class MestPatientTypeRepository
{
    protected $mestPatientType;
    public function __construct(MestPatientType $mestPatientType)
    {
        $this->mestPatientType = $mestPatientType;
    }

    public function applyJoins()
    {
        return $this->mestPatientType
            ->leftJoin('his_medi_stock as medi_stock', 'medi_stock.id', '=', 'his_mest_patient_type.medi_stock_id')
            ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_mest_patient_type.patient_type_id')
            ->select(
                'his_mest_patient_type.*',
                'medi_stock.medi_stock_code',
                'medi_stock.medi_stock_name',
                'patient_type.patient_type_code',
                'patient_type.patient_type_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query
                ->where(DB::connection('oracle_his')->raw('medi_stock.medi_stock_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('medi_stock.medi_stock_name'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('patient_type.patient_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('patient_type.patient_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyMediStockIdFilter($query, $mediStockId)
    {
        if ($mediStockId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.medi_stock_id'), $mediStockId);
        }
        return $query;
    }
    public function applyPatientTypeIdFilter($query, $patientTypeId)
    {
        if ($patientTypeId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_mest_patient_type.patient_type_id'), $patientTypeId);
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
                    if (in_array($key, ['patient_type_code', 'patient_type_name'])) {
                        $query->orderBy('patient_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_mest_patient_type.' . $key, $item);
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
        return $this->mestPatientType->find($id);
    }
    public function getByMediStockIdAndPatientTypeIds($mediStockId, $patientTypeIds)
    {
        return $this->mestPatientType->where('medi_stock_id', $mediStockId)->whereIn('patient_type_id', $patientTypeIds)->get();
    }
    public function getByPatientTypeIdAndMediStockIds($patientTypeId, $mediStockIds)
    {
        return $this->mestPatientType->whereIn('medi_stock_id', $mediStockIds)->where('patient_type_id', $patientTypeId)->get();
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function deleteByMediStockId($id)
    {
        $ids = $this->mestPatientType->where('medi_stock_id', $id)->pluck('id')->toArray();
        $this->mestPatientType->where('medi_stock_id', $id)->delete();
        return $ids;
    }
    public function deleteByPatientTypeId($id)
    {
        $ids = $this->mestPatientType->where('patient_type_id', $id)->pluck('id')->toArray();
        $this->mestPatientType->where('patient_type_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_mest_patient_type.id', '=', $id)->first();
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
