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
    public function applyJoinsXuTriKham()
    {
        return $this->patient
            ->select([
                'his_patient.note',
                'his_patient.work_place',
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
