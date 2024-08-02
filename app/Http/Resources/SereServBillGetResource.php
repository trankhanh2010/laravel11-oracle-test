<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SereServBillGetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
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
            'sereServId' => $this->sere_serv_id,
            'billId' => $this->bill_id,
            'price' => $this->price,
            'vatRatio' => $this->vat_ratio,
            'tdlTreatmentId' => $this->tdl_treatment_id,
            'tdlBillTypeId' => $this->tdl_bill_type_id,
            'tdlServiceReqId' => $this->tdl_service_req_id,
            'tdlPrimaryPrice' => $this->tdl_primary_price,
            'tdlAmount' => $this->tdl_amount,
            'tdlPrice' => $this->tdl_price,
            'tdlOriginalPrice' => $this->tdl_original_price,
            'tdlVatRatio' => $this->tdl_vat_ratio,
            'tdlServiceTypeId' => $this->tdl_service_type_id,
            'tdlHeinServiceTypeId' => $this->tdl_hein_service_type_id,
            'tdlTotalHeinPrice' => $this->tdl_total_hein_price,
            'tdlTotalPatientPrice' => $this->tdl_total_patient_price,
            'tdlTotalPatientPriceBhyt' => $this->tdl_total_patient_price_bhyt,
            'tdlServiceId' => $this->tdl_service_id,
            'tdlServiceCode' => $this->tdl_service_code,
            'tdlServiceName' => $this->tdl_service_name,
            'tdlServiceUnitId' => $this->tdl_service_unit_id,
            'tdlPatientTypeId' => $this->tdl_patient_type_id,
            'tdlRequestDepartmentId' => $this->tdl_request_department_id,
            'tdlExecuteDepartmentId' => $this->tdl_execute_department_id,
            'tdlRealPrice' => $this->tdl_real_price,
            'tdlRealPatientPrice' => $this->tdl_real_patient_price,
            'tdlRealHeinPrice' => $this->tdl_real_hein_price ,
        ];
    }
}
