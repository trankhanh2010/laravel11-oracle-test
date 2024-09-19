<?php 
namespace App\Repositories;

use App\Models\HIS\MedicineUseForm;
use Illuminate\Support\Facades\DB;

class MedicineUseFormRepository
{
    protected $medicineUseForm;
    public function __construct(MedicineUseForm $medicineUseForm)
    {
        $this->medicineUseForm = $medicineUseForm;
    }

    public function applyJoins()
    {
        return $this->medicineUseForm
            ->select(
                'his_medicine_use_form.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_use_form.medicine_use_form_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medicine_use_form.medicine_use_form_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_use_form.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_medicine_use_form.' . $key, $item);
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
        return $this->medicineUseForm->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->medicineUseForm::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'medicine_use_form_code' => $request->medicine_use_form_code,
            'medicine_use_form_name' => $request->medicine_use_form_name,
            'num_order' => $request->num_order,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'medicine_use_form_code' => $request->medicine_use_form_code,
            'medicine_use_form_name' => $request->medicine_use_form_name,
            'num_order' => $request->num_order,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_medicine_use_form.id','=', $id)->first();
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