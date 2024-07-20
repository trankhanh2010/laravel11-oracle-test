<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Controllers\Controller;
use App\Models\HIS\Treatment;
use App\Models\HIS\TreatmentBedRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TreatmentBedRoomController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatment_bed_room = new TreatmentBedRoom();
        $this->treatment = new Treatment();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->treatment->getConnection()->getSchemaBuilder()->hasColumn($this->treatment->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

    }
  public function treatment_bed_room_get_L_view(Request $request)
  {
      $select = [
        "his_treatment_bed_room.ID",
        "his_treatment_bed_room.TREATMENT_ID",
        "his_treatment_bed_room.CO_TREATMENT_ID",
        "his_treatment_bed_room.ADD_TIME",
        "his_treatment_bed_room.REMOVE_TIME",
        "his_treatment_bed_room.BED_ROOM_ID",

        "treatment.TDL_PATIENT_FIRST_NAME",
        'treatment.TREATMENT_CODE', 
        'treatment.TDL_PATIENT_LAST_NAME', 
        'treatment.TDL_PATIENT_NAME', 
        'treatment.TDL_PATIENT_DOB', 
        'treatment.TDL_PATIENT_GENDER_NAME', 
        'treatment.TDL_PATIENT_CODE', 
        'treatment.TDL_PATIENT_ADDRESS', 
        'treatment.TDL_HEIN_CARD_NUMBER', 
        'treatment.TDL_HEIN_MEDI_ORG_CODE', 
        'treatment.ICD_CODE', 
        'treatment.ICD_NAME', 
        'treatment.ICD_TEXT', 
        'treatment.ICD_SUB_CODE', 
        'treatment.TDL_PATIENT_GENDER_ID', 
        'treatment.TDL_HEIN_MEDI_ORG_NAME', 
        'treatment.TDL_TREATMENT_TYPE_ID', 
        'treatment.EMR_COVER_TYPE_ID', 
        'treatment.CLINICAL_IN_TIME', 
        'treatment.CO_TREAT_DEPARTMENT_IDS', 
        'treatment.LAST_DEPARTMENT_ID', 
        'treatment.TDL_PATIENT_UNSIGNED_NAME', 
        'treatment.TREATMENT_METHOD', 
        'treatment.TDL_HEIN_CARD_FROM_TIME', 
        'treatment.TDL_HEIN_CARD_TO_TIME',

        'patient_type.patient_type_code',
        'patient_type.patient_type_name',

        'last_department.department_code',
        'last_department.department_name',

        'patient.note',

        'bed_room.bed_room_name',

      ];
      $param = [
          'treatment',
          'treatment.patient_type:id,patient_type_code,patient_type_name',
          'treatment.last_department:id,department_code,department_name',
          'treatment.patient:id,note',
          'bed_room:id,bed_room_name'
      ];
      $keyword = mb_strtolower($this->keyword, 'UTF-8');
      $data = $this->treatment_bed_room
        ->leftJoin('his_treatment as treatment', 'treatment.id', '=', 'his_treatment_bed_room.treatment_id')
        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'treatment.tdl_patient_type_id')
        ->leftJoin('his_department as last_department', 'last_department.id', '=', 'treatment.last_department_id')
        ->leftJoin('his_patient as patient', 'patient.id', '=', 'treatment.patient_id')
        ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'his_treatment_bed_room.bed_room_id')
        ->select($select);
      if ($keyword != null) {
          $data = $data->where(function ($query) use ($keyword) {
              $query = $query->where(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(treatment.TDL_PATIENT_LAST_NAME))'), 'like', '%' . $keyword . '%')
                  ->orWhere(DB::connection('oracle_his')->raw('FUN_CONVERT_TO_UNSIGN(lower(treatment.TREATMENT_CODE))'), 'like', '%' . $keyword . '%');
          });
      }
      if (!$this->is_include_deleted) {
          $data = $data->where(function ($query) {
              $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_delete'), 0);
          });
      }
      if ($this->is_active !== null) {
          $data = $data->where(function ($query) {
              $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.is_active'), $this->is_active);
          });
      }
      if ($this->bed_room_ids != null) {
          $data = $data->where(function ($query) {
              $query = $query->whereIn(DB::connection('oracle_his')->raw('his_treatment_bed_room.bed_room_id'), $this->bed_room_ids);
          });
      }
      if ($this->add_time_to != null) {
          $data = $data->where(function ($query) {
              $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.add_time'), '>=', $this->add_time_to);
          });
      }
      if (!$this->is_in_room) {
        if ($this->add_time_from != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.remove_time'), '<=', $this->add_time_from);
            });
        }
      }
      if ($this->treatment_bed_room_id == null) {
          $count = $data->count();
          if ($this->order_by != null) {
              foreach ($this->order_by as $key => $item) {
                  $data->orderBy('his_treatment_bed_room.' . $key, $item);
              }
          }
          $data = $data
              ->skip($this->start)
              ->take($this->limit)
              ->get();
      } else {
          $data = $data->where(function ($query) {
              $query = $query->where(DB::connection('oracle_his')->raw('his_treatment_bed_room.id'), $this->treatment_bed_room_id);
          });
          $data = $data
              ->skip($this->start)
              ->take($this->limit)
              ->first();
      }

      $param_return = [
          'start' => $this->start,
          'limit' => $this->limit,
          'count' => $count ?? null,
          'is_include_deleted' => $this->is_include_deleted ?? false,
          'is_active' => $this->is_active,
          'treatment_bed_room_id' => $this->treatment_bed_room_id,
          'is_in_room' => $this->is_in_room,
          'add_time_to' => $this->add_time_to,
          'add_time_from' => $this->add_time_from,
          'bed_room_ids' => $this->bed_room_ids,
          'keyword' => $this->keyword,
          'order_by' => $this->order_by_request
      ];
      return return_data_success($param_return, $data);

  }
}
