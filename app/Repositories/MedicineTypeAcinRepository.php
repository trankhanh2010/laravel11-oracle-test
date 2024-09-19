<?php 
namespace App\Repositories;

use App\Models\HIS\MedicineTypeAcin;
use Illuminate\Support\Facades\DB;

class MedicineTypeAcinRepository
{
    protected $medicineTypeAcin;
    public function __construct(MedicineTypeAcin $medicineTypeAcin)
    {
        $this->medicineTypeAcin = $medicineTypeAcin;
    }

    public function applyJoins()
    {
        return $this->medicineTypeAcin
        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'his_medicine_type_acin.medicine_type_id')
        ->leftJoin('his_active_ingredient as active_ingredient', 'active_ingredient.id', '=', 'his_medicine_type_acin.active_ingredient_id')
            ->select(
                'his_medicine_type_acin.*',
                'medicine_type.medicine_type_code',
                'medicine_type.medicine_type_name',
                'active_ingredient.active_ingredient_code',
                'active_ingredient.active_ingredient_name'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query                        
            ->where(DB::connection('oracle_his')->raw('medicine_type.medicine_type_code'), 'like', $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('medicine_type.medicine_type_name'), 'like', $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('active_ingredient.active_ingredient_code'), 'like', $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('active_ingredient.active_ingredient_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.is_active'), $isActive);
        }
        return $query;
    }
    public function applyMedicineTypeIdFilter($query, $medicineTypeId)
    {
        if ($medicineTypeId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.medicine_type_id'), $medicineTypeId);
        }
        return $query;
    }
    public function applyActiveIngredientIdFilter($query, $activeIngredientId)
    {
        if ($activeIngredientId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_type_acin.active_ingredient_id'), $activeIngredientId);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['medicine_type_code', 'medicine_type_name'])) {
                        $query->orderBy('medicine_type.' . $key, $item);
                    }
                    if (in_array($key, ['active_ingredient_code', 'active_ingredient_name'])) {
                        $query->orderBy('active_ingredient.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_medicine_type_acin.' . $key, $item);
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
        return $this->medicineTypeAcin->find($id);
    }
    public function getByMedicineTypeIdAndActiveIngredientIds($medicineTypeId, $activeIngredientIds)
    {
        return $this->medicineTypeAcin->where('medicine_type_id', $medicineTypeId)->whereIn('active_ingredient_id',$activeIngredientIds)->get();
    }
    public function getByActiveIngredientIdAndMedicineTypeIds($activeIngredientId, $medicineTypeIds)
    {
        return $this->medicineTypeAcin->whereIn('medicine_type_id', $medicineTypeIds)->where('active_ingredient_id',$activeIngredientId)->get();
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function deleteByMedicineTypeId($id){
        $ids = $this->medicineTypeAcin->where('medicine_type_id', $id)->pluck('id')->toArray();
        $this->medicineTypeAcin->where('medicine_type_id', $id)->delete();
        return $ids;
    }
    public function deleteByActiveIngredientId($id){
        $ids = $this->medicineTypeAcin->where('active_ingredient_id', $id)->pluck('id')->toArray();
        $this->medicineTypeAcin->where('active_ingredient_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_medicine_type_acin.id','=', $id)->first();
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