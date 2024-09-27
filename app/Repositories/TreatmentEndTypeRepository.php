<?php 
namespace App\Repositories;

use App\Models\HIS\TreatmentEndType;
use Illuminate\Support\Facades\DB;

class TreatmentEndTypeRepository
{
    protected $treatmentEndType;
    public function __construct(TreatmentEndType $treatmentEndType)
    {
        $this->treatmentEndType = $treatmentEndType;
    }

    public function applyJoins()
    {
        return $this->treatmentEndType
            ->select(
                'his_treatment_end_type.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_treatment_end_type.treatment_end_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_treatment_end_type.treatment_end_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_treatment_end_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_treatment_end_type.' . $key, $item);
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
        return $this->treatmentEndType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->treatmentEndType::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'treatment_end_type_code' => $request->treatment_end_type_code,
            'treatment_end_type_name' => $request->treatment_end_type_name,
            'end_code_prefix' => $request->end_code_prefix,
            'is_for_out_patient' => $request->is_for_out_patient,
            'is_for_in_patient' => $request->is_for_in_patient,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'treatment_end_type_code' => $request->treatment_end_type_code,
            'treatment_end_type_name' => $request->treatment_end_type_name,
            'end_code_prefix' => $request->end_code_prefix,
            'is_for_out_patient' => $request->is_for_out_patient,
            'is_for_in_patient' => $request->is_for_in_patient,
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
            $data = $data->where('his_treatment_end_type.id','=', $id)->first();
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