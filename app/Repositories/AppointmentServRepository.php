<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\AppointmentServ;
use Illuminate\Support\Facades\DB;

class AppointmentServRepository
{
    protected $appointmentServ;
    public function __construct(AppointmentServ $appointmentServ)
    {
        $this->appointmentServ = $appointmentServ;
    }

    public function applyJoins()
    {
        return $this->appointmentServ
            ->select(
                'his_appointment_serv.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_appointment_serv.appointment_period_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_appointment_serv.appointment_period_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_appointment_serv.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_appointment_serv.' . $key, $item);
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
        return $this->appointmentServ->find($id);
    }
    public function getByTreatmentId($treatmentId)
    {
        return $this->appointmentServ
        ->leftJoin('his_service', 'his_service.id', '=', 'his_appointment_serv.service_id')
        ->leftJoin('his_service_type', 'his_service_type.id', '=', 'his_service.service_type_id')
        ->leftJoin('his_patient_type', 'his_patient_type.id', '=', 'his_appointment_serv.patient_type_id')
        ->select([
            'his_appointment_serv.id as key',
            'his_appointment_serv.id',
            'his_appointment_serv.treatment_id',
            'his_appointment_serv.service_id',
            'his_service_type.service_type_code',
            'his_service_type.service_type_name',
            'his_service.service_code',
            'his_service.service_name',
            'his_appointment_serv.amount',
            'his_appointment_serv.tdl_patient_id',
            'his_appointment_serv.patient_type_id',
            'his_patient_type.patient_type_code',
        ])->where('treatment_id', $treatmentId)->get();
    }
}
