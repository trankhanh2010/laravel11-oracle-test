<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SereServDepositGetResource extends JsonResource
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
            'depositId' => $this->deposit_id,
            'amount' => $this->amount,
            'tdlTreatmentId' => $this->tdl_treatment_id,
            'tdlServiceReqId' => $this->tdl_service_req_id,
            'tdlServiceId' => $this->tdl_service_id,
            'tdlServiceCode' => $this->tdl_service_code,
            'tdlServiceName' => $this->tdl_service_name,
            'tdlServiceTypeId' => $this->tdl_service_type_id,
            'tdlServiceUnitId' => $this->tdl_service_unit_id,
            'tdlPatientTypeId' => $this->tdl_patient_type_id,
            'tdlHeinServiceTypeId' => $this->tdl_hein_service_type_id,
            'tdlRequestDepartmentId' => $this->tdl_request_department_id,
            'tdlExecuteDepartmentId' => $this->tdl_execute_department_id,
            'tdlAmount' => $this->tdl_amount,
            'tdlHeinLimitPrice' => $this->tdl_hein_limit_price,
            'tdlVirPrice' => $this->tdl_vir_price,
            'tdlVirPriceNoAddPrice' => $this->tdl_vir_price_no_add_price,
            'tdlVirHeinPrice' => $this->tdl_vir_hein_price,
            'tdlVirTotalPrice' => $this->tdl_vir_total_price,
            'tdlVirTotalHeinPrice' => $this->tdl_vir_total_hein_price,
            'tdlVirTotalPatientPrice' => $this->tdl_vir_total_patient_price,
            'serviceReqSttId' => $this->service_req_stt_id,
            'serviceReqTypeId' => $this->service_req_type_id,
            'serviceReqCode' => $this->service_req_code,
            'intructionTime' => $this->intruction_time,
            'serviceTypeCode' => $this->service_type_code,
            'serviceTypeName' => $this->service_type_name,
            'transactionCode' => $this->transaction_code,
            'payFormId' => $this->pay_form_id,
            'payFormCode' => $this->pay_form_code,
            'payFormName' => $this->pay_form_name,
        ];
    }
}
