<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Patient;
use Illuminate\Support\Facades\DB;

class PatientRepository
{
    protected $patient;
    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
    }

    public function applyJoins()
    {
        return $this->patient
            ->select(
                'his_patient.*'
            );
    }
    public function applyJoinsTimThongTinBenhNhan()
    {
        return $this->patient
        ->leftJoin('his_work_place','his_work_place.id', '=', 'his_patient.work_place_id')
            ->select(
                'his_patient.id',
                'his_patient.patient_code',
                'his_patient.vir_patient_name',
                'his_patient.gender_id',
                'his_patient.dob',

                'his_patient.commune_code',
                'his_patient.province_code',
                'his_patient.address',
            
                'his_patient.work_place',
                'his_work_place.work_place_name',
            );
    }
    public function applyJoinsLayThongTinBenhNhan()
    {
        return $this->patient
        ->leftJoin('his_work_place','his_work_place.id', '=', 'his_patient.work_place_id')
            ->select(
                'his_patient.id',
                'his_patient.patient_code',
                'his_patient.vir_patient_name',
                'his_patient.gender_id',
                'his_patient.dob',
                'his_patient.career_id',

                'his_patient.commune_code',
                'his_patient.province_code',
                'his_patient.address',
                'his_patient.phone',

                'his_patient.father_name',
                'his_patient.mother_name',
                'his_patient.relative_name',
                'his_patient.relative_type',
                'his_patient.relative_address',
                'his_patient.relative_phone',

                'his_patient.ethnic_code',
                'his_patient.national_code',

                'his_patient.work_place',
                'his_work_place.work_place_name',
                'his_patient.cccd_number',
                'his_patient.cccd_date',
                'his_patient.cccd_place',
            );
    }
    public function applyJoinsXuTriKham()
    {
        return $this->patient
            ->select([
                'his_patient.note',
                'his_patient.work_place',
                'his_patient.cccd_number',
                'his_patient.cccd_date',
                'his_patient.cccd_place',
                'his_patient.cmnd_number',
                'his_patient.cmnd_date',
                'his_patient.cmnd_place',
            ]);
    }
    public function applyWithParamTimThongTinBenhNhan($query){
        return $query->with([
            'cac_lan_kham',
        ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_patient.patient_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_patient.patient_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyPhoneFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient.phone'), $param);
        }
        return $query;
    }
    public function applyCccdNumberFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient.cccd_number'), $param);
        }
        return $query;
    }
    public function applyPatientCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient.patient_code'), $param);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_patient.' . $key, $item);
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
        return $this->patient->find($id);
    }
    public function getByPatientCode($code)
    {
        return $this->patient->where('patient_code', $code)->first();
    }
}
