<?php 
namespace App\Repositories;

use App\Models\HIS\MedicineGroup;
use Illuminate\Support\Facades\DB;

class MedicineGroupRepository
{
    protected $medicineGroup;
    public function __construct(MedicineGroup $medicineGroup)
    {
        $this->medicineGroup = $medicineGroup;
    }

    public function applyJoins()
    {
        return $this->medicineGroup
            ->select(
                'his_medicine_group.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_group.medicine_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medicine_group.medicine_group_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_group.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_medicine_group.' . $key, $item);
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
        return $this->medicineGroup->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->medicineGroup::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'medicine_group_code' => $request->medicine_group_code,
            'medicine_group_name' => $request->medicine_group_name,
            'num_order' =>  $request->num_order,      
            'is_separate_printing'  => $request->is_separate_printing,
            'is_numbered_tracking'  => $request->is_numbered_tracking,
            'is_warning'  => $request->is_warning,
            'number_day'  => $request->number_day,
            'is_auto_treatment_day_count'  => $request->is_auto_treatment_day_count, 

        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'medicine_group_code' => $request->medicine_group_code,
            'medicine_group_name' => $request->medicine_group_name,
            'num_order' =>  $request->num_order,      
            'is_separate_printing'  => $request->is_separate_printing,
            'is_numbered_tracking'  => $request->is_numbered_tracking,
            'is_warning'  => $request->is_warning,
            'number_day'  => $request->number_day,
            'is_auto_treatment_day_count'  => $request->is_auto_treatment_day_count, 
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
            $data = $data->where('his_medicine_group.id','=', $id)->first();
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