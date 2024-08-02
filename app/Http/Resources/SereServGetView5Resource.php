<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SereServGetView5Resource extends JsonResource
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
            // 'modifier' => $this->modifier,
            'appCreator' => $this->app_creator,
            'appModifier' => $this->app_modifier,
            'isActive' => $this->is_active,
            'isDelete' => $this->is_delete,
            'serviceId' => $this->service_id,
            'serviceReqId' => $this->service_req_id,
            'patientTypeId' => $this->patient_type_id,
            'primaryPatientTypeId' => $this->primary_patient_type_id,
            'primaryPrice' => $this->primary_price,
            'limitPrice' => $this->limit_price,
            'heinApprovalId' => $this->hein_approval_id,
            'jsonPatientTypeAlter' => $this->json_patient_type_alter,
            'amount' => $this->amount,
            'price' => $this->price,
            'originalPrice' => $this->original_price,
            'heinPrice' => $this->hein_price,
            'heinRatio' => $this->hein_ratio,
            'heinLimitPrice' => $this->hein_limit_price,
            'vatRatio' => $this->vat_ratio,
            'isSentExt' => $this->is_sent_ext,
            'heinCardNumber' => $this->hein_card_number,
            'tdlIntructionTime' => $this->tdl_intruction_time,
            'tdlIntructionDate' => $this->tdl_intruction_date,
            'tdlPatientId' => $this->tdl_patient_id,
            'tdlTreatmentId' => $this->tdl_treatment_id,
            'tdlTreatmentCode' => $this->tdl_treatment_code,
            'tdlServiceCode' => $this->tdl_service_code,
            'tdlServiceName' => $this->tdl_service_name,
            'tdlHeinServiceBhytCode' => $this->tdl_hein_service_bhyt_code,
            'tdlHeinServiceBhytName' => $this->tdl_hein_service_bhyt_name,
            'tdlServiceTypeId' => $this->tdl_service_type_id,
            'tdlServiceUnitId' => $this->tdl_service_unit_id,
            'tdlHeinServiceTypeId' => $this->tdl_hein_service_type_id,
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
            'tdlPacsTypeCode' => $this->tdl_pacs_type_code,
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
            'tdlRequestUserTitle' => $this->tdl_request_user_title,
            'serviceTypeCode' => $this->service_type_code,
            'serviceTypeName' => $this->service_type_name,
            'patientTypeName' => $this->patient_type_name,
            'serviceUnitCode' => $this->service_unit_code,
            'serviceUnitName' => $this->service_unit_name,
            'requestRoomIsExam' => $this->request_room_is_exam,
            'requestRoomCode' => $this->request_room_code,
            'requestRoomName' => $this->request_room_name,
            'serviceReqSttId' => $this->service_req_stt_id,
        ];
    }
}
