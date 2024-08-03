<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeseDepoRepayGetViewResource extends JsonResource
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
            'sereServDepositId' => $this->sere_serv_deposit_id,
            'repayId' => $this->repay_id,
            'amount' => $this->amount,
            'isCancel' => $this->is_cancel,
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
            'tdlVirPrice' => $this->tdl_vir_price,
            'tdlVirPriceNoAddPrice' => $this->tdl_vir_price_no_add_price,
            'tdlVirHeinPrice' => $this->tdl_vir_hein_price,
            'tdlVirTotalPrice' => $this->tdl_vir_total_price,
            'tdlVirTotalHeinPrice' => $this->tdl_vir_total_hein_price,
            'tdlVirTotalPatientPrice' => $this->tdl_vir_total_patient_price,
            'sereServId' => $this->sere_serv_id,

        ];
    }
}
