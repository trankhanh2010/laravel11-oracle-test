<?php 
namespace App\Repositories;

use App\Models\HIS\PatientType;
use Illuminate\Support\Facades\DB;

class PatientTypeRepository
{
    protected $patientType;
    public function __construct(PatientType $patientType)
    {
        $this->patientType = $patientType;
    }

    public function applyJoins()
    {
        return $this->patientType
            ->select(
                'his_patient_type.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type.patient_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_patient_type.patient_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_patient_type.' . $key, $item);
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
        return $this->patientType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->patientType::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'patient_type_code'  => $request->patient_type_code,    
            'patient_type_name'  => $request->patient_type_name,
            'description' => $request->description,  
            'priority'   =>  $request->priority, 

            'base_patient_type_id' => $request->base_patient_type_id, 
            'other_pay_source_ids'  => $request->other_pay_source_ids,
            'treatment_type_ids'  => $request->treatment_type_ids,      
            'is_copayment' =>  $request->is_copayment,

            'is_not_use_for_patient' => $request->is_not_use_for_patient,
            'is_not_for_kiosk' => $request->is_not_for_kiosk,     
            'is_addition_required' => $request->is_addition_required,   
            'is_addition' => $request->is_addition,               

            'is_not_service_bill' => $request->is_not_service_bill,        
            'is_check_fee_when_assign' => $request->is_check_fee_when_assign,    
            'is_check_finish_cls_when_pres'=> $request->is_check_finish_cls_when_pres, 
            'is_check_fee_when_pres' => $request->is_check_fee_when_pres,        

            'is_not_edit_assign_service' =>  $request->is_not_edit_assign_service,  
            'is_showing_out_stock_by_def' => $request->is_showing_out_stock_by_def,  
            'is_not_check_fee_when_exp_pres' => $request->is_not_check_fee_when_exp_pres,
            'is_for_sale_exp' =>  $request->is_for_sale_exp,             

            'must_be_guaranteed' =>  $request->must_be_guaranteed,        
            'is_ration' =>  $request->is_ration                    
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'patient_type_code'  => $request->patient_type_code,    
            'patient_type_name'  => $request->patient_type_name,
            'description' => $request->description,  
            'priority'   =>  $request->priority, 

            'base_patient_type_id' => $request->base_patient_type_id, 
            'other_pay_source_ids'  => $request->other_pay_source_ids,
            'treatment_type_ids'  => $request->treatment_type_ids,      
            'is_copayment' =>  $request->is_copayment,

            'is_not_use_for_patient' => $request->is_not_use_for_patient,
            'is_not_for_kiosk' => $request->is_not_for_kiosk,     
            'is_addition_required' => $request->is_addition_required,   
            'is_addition' => $request->is_addition,               

            'is_not_service_bill' => $request->is_not_service_bill,        
            'is_check_fee_when_assign' => $request->is_check_fee_when_assign,    
            'is_check_finish_cls_when_pres'=> $request->is_check_finish_cls_when_pres, 
            'is_check_fee_when_pres' => $request->is_check_fee_when_pres,        

            'is_not_edit_assign_service' =>  $request->is_not_edit_assign_service,  
            'is_showing_out_stock_by_def' => $request->is_showing_out_stock_by_def,  
            'is_not_check_fee_when_exp_pres' => $request->is_not_check_fee_when_exp_pres,
            'is_for_sale_exp' =>  $request->is_for_sale_exp,             

            'must_be_guaranteed' =>  $request->must_be_guaranteed,        
            'is_ration' =>  $request->is_ration,              
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
        if ($id != null) {
            $data = $data->where('his_patient_type.id', '=', $id)->first();
            if ($data) {
                $data->toArray();
            }
        } else {
            $data = $data->get();
            $data->toArray();
        }
        return $data;
    }
}