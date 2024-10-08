<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SereServResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'createTime' => $this->create_time,
            'modifyTime' => $this->modify_time,
            'creator' => $this->creator,
            'modifier' => $this->modifier,
            'appCreator' => $this->app_creator,
            'appModifier' => $this->app_modifier,
            'isActive' => $this->is_active,
            'isDelete' => $this->is_delete,
            'serviceId' => $this->service_id,
            'serviceReqId' => $this->service_req_id,
            'patientTypeId' => $this->patient_type_id,
            'primaryPrice' => $this->primary_price,
            'amount' => $this->amount,
            'price' => $this->price,
            'originalPrice' => $this->original_price,
            'vatRatio' => $this->vat_ratio,
            'medicineId' => $this->medicine_id,
            'expMestMedicineId' => $this->exp_mest_medicine_id,
            'tdlIntructionTime' =>  $this->tdl_intruction_time,
            'tdlIntructionDate' => $this->tdl_intruction_date,
            'tdlPatientId' => $this->tdl_patient_id,
            'tdlTreatmentId' => $this->tdl_treatment_id,
            'tdlTreatmentCode' => $this->tdl_treatment_code,
            'tdlServiceCode' => $this->tdl_service_code,
            'tdlServiceName' => $this->tdl_service_name,
            'tdlHeinServiceBhytName' => $this->tdl_hein_service_bhyt_name,
            'tdlServiceTypeId' => $this->tdl_service_type_id,
            'tdlServiceUnitId' => $this->tdl_service_unit_id,
            'tdlHeinServiceTypeId' => $this->tdl_hein_service_type_id,
            'tdlActiveIngrBhytCode' => $this->tdl_active_ingr_bhyt_code,
            'tdlActiveIngrBhytName' => $this->tdl_active_ingr_bhyt_name,
            'tdlMedicineConcentra' => $this->tdl_medicine_concentra,
            'tdlMedicineRegisterNumber' => $this->tdl_medicine_register_number,
            'tdlMedicinePackageNumber' => $this->tdl_medicine_package_number,
            'tdlServiceReqCode' => $this->tdl_service_req_code,
            'tdlRequestRoomId' => $this->tdl_request_room_id,
            'tdlRequestDepartmentId' => $this->tdl_request_department_id,
            'tdlRequestLoginname' => $this->tdl_request_loginname,
            'tdlRequestUsername' => $this->tdl_request_username,
            'tdlExecuteRoomId' => $this->tdl_execute_room_id,
            'tdlExecuteDepartmentId' => $this->tdl_execute_department_id,
            'tdlExecuteBranchId' => $this->tdl_execute_branch_id,
            'tdlServiceReqTypeId' => $this->tdl_service_req_type_id,
            'tdlHstBhytCode' => $this->tdl_hst_bhyt_code,
            'virPrice' => $this->vir_price,
            'virPriceNoAddPrice' => $this->vir_price_no_add_price,
            'virPriceNoExpend' => $this->vir_price_no_expend,
            'virHeinPrice' => $this->vir_hein_price,
            'virPatientPrice' => $this->vir_patient_price,
            'virPatientPriceBhyt' => $this->vir_patient_price_bhyt,
            'virTotalPrice' => $this->vir_total_price,
            'virTotalPriceNoAddPrice' => $this->vir_total_price_no_add_price,
            'virTotalPriceNoExpend' => $this->vir_total_price_no_expend,
            'virTotalHeinPrice' => $this->vir_total_hein_price,
            'virTotalPatientPrice' => $this->vir_total_patient_price,
            'virTotalPatientPriceBhyt' => $this->vir_total_patient_price_bhyt,
            'virTotalPatientPriceNoDc' => $this->vir_total_patient_price_no_dc,
            'virTotalPatientPriceTemp' => $this->vir_total_patient_price_temp,

        ];
    }
}
