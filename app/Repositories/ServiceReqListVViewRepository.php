<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Room;
use App\Models\View\ServiceReqListVView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceReqListVViewRepository
{
    protected $serviceReqListVView;
    protected $room;
    public function __construct(ServiceReqListVView $serviceReqListVView)
    {
        $this->serviceReqListVView = $serviceReqListVView;
    }

    public function applyJoins()
    {
        return $this->serviceReqListVView
            ->select(
                'xa_v_his_service_req_list.id',
                'xa_v_his_service_req_list.create_time',
                'xa_v_his_service_req_list.modify_time',
                'xa_v_his_service_req_list.creator',
                'xa_v_his_service_req_list.modifier',
                'xa_v_his_service_req_list.app_creator',
                'xa_v_his_service_req_list.app_modifier',
                'xa_v_his_service_req_list.is_active',
                'xa_v_his_service_req_list.is_delete',
                'xa_v_his_service_req_list.is_no_execute',
                'xa_v_his_service_req_list.intruction_time',
                'xa_v_his_service_req_list.intruction_date',
                'xa_v_his_service_req_list.treatment_id',
                'xa_v_his_service_req_list.tracking_id',
                'xa_v_his_service_req_list.service_req_code',
                'xa_v_his_service_req_list.note',
                'xa_v_his_service_req_list.CONCLUSION_CLINICAL',
                'xa_v_his_service_req_list.CONCLUSION_SUBCLINICAL',
                'xa_v_his_service_req_list.service_req_stt_code',
                'xa_v_his_service_req_list.service_req_stt_name',
                'xa_v_his_service_req_list.tdl_patient_id',
                'xa_v_his_service_req_list.request_department_code',
                'xa_v_his_service_req_list.request_department_name',
            );
    }
    public function applyJoinsChiDinhCuChiDinhDichVuKyThuat()
    {
        return $this->serviceReqListVView
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'xa_v_his_service_req_list.service_req_type_id')
            ->select([
                'xa_v_his_service_req_list.id',
                'xa_v_his_service_req_list.service_req_code',
                'xa_v_his_service_req_list.intruction_time',
                'xa_v_his_service_req_list.intruction_date',
                'xa_v_his_service_req_list.request_loginname',
                'xa_v_his_service_req_list.request_username',
                'his_service_req_type.service_req_type_code',
                'his_service_req_type.service_req_type_name',
            ]);
    }
    public function applyJoinsChiTietDon()
    {
        return $this->serviceReqListVView
            ->with(['chi_tiet_don'])
            ->select([
                'xa_v_his_service_req_list.id',
                'xa_v_his_service_req_list.service_req_code',
                'xa_v_his_service_req_list.intruction_time',
                'xa_v_his_service_req_list.intruction_date',
            ]);
    }
    public function applyJoinsChiDinh()
    {
        return $this->serviceReqListVView
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'xa_v_his_service_req_list.service_req_type_id')
            ->leftJoin('v_his_room as request_room', 'request_room.id', '=', 'xa_v_his_service_req_list.request_room_id')
            ->leftJoin('v_his_room as execute_room', 'execute_room.id', '=', 'xa_v_his_service_req_list.execute_room_id')
            ->leftJoin('his_ration_time as ration_time', 'ration_time.id', '=', 'xa_v_his_service_req_list.ration_time_id')
            ->leftJoin('his_department as execute_department', 'execute_department.id', '=', 'xa_v_his_service_req_list.execute_department_id')
            ->leftJoin('his_department as request_department', 'request_department.id', '=', 'xa_v_his_service_req_list.request_department_id')

            ->select([
                'xa_v_his_service_req_list.id as key',

                'xa_v_his_service_req_list.id',
                'xa_v_his_service_req_list.is_active',
                'xa_v_his_service_req_list.is_delete',
                'xa_v_his_service_req_list.is_no_execute',
                'xa_v_his_service_req_list.service_req_stt_code',
                'xa_v_his_service_req_list.service_req_stt_name',

                'xa_v_his_service_req_list.service_req_code',
                'xa_v_his_service_req_list.tdl_treatment_code',
                'xa_v_his_service_req_list.tdl_patient_code',
                'his_service_req_type.service_req_type_code',
                'his_service_req_type.service_req_type_name',
                'xa_v_his_service_req_list.tdl_patient_name',

                'xa_v_his_service_req_list.execute_room_id',
                'execute_room.room_code as execute_room_code',
                'execute_room.room_name as execute_room_name',
                'xa_v_his_service_req_list.request_room_id',
                'request_room.room_code as request_room_code',
                'request_room.room_name as request_room_name',

                'xa_v_his_service_req_list.is_main_exam',
                'xa_v_his_service_req_list.intruction_time',

                'xa_v_his_service_req_list.request_loginname',
                'xa_v_his_service_req_list.request_username',
                'xa_v_his_service_req_list.execute_loginname',
                'xa_v_his_service_req_list.execute_username',

                'ration_time.ration_time_code',
                'ration_time.ration_time_name',
                'xa_v_his_service_req_list.tdl_patient_dob',
                'xa_v_his_service_req_list.create_time',
                'xa_v_his_service_req_list.creator',
                'xa_v_his_service_req_list.modify_time',
                'xa_v_his_service_req_list.modifier',

                'xa_v_his_service_req_list.session_code',
                'xa_v_his_service_req_list.use_time',
                'xa_v_his_service_req_list.parent_id',

                'xa_v_his_service_req_list.icd_code',
                'xa_v_his_service_req_list.icd_name',
                'xa_v_his_service_req_list.icd_sub_code',
                'xa_v_his_service_req_list.icd_text',
                'xa_v_his_service_req_list.tracking_id',

            ]);
    }

    public function applyJoinsChiDinhChiTiet()
    {
        return $this->serviceReqListVView
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'xa_v_his_service_req_list.service_req_type_id')
            ->leftJoin('v_his_room as request_room', 'request_room.id', '=', 'xa_v_his_service_req_list.request_room_id')
            ->leftJoin('v_his_room as execute_room', 'execute_room.id', '=', 'xa_v_his_service_req_list.execute_room_id')
            ->leftJoin('his_ration_time as ration_time', 'ration_time.id', '=', 'xa_v_his_service_req_list.ration_time_id')
            ->leftJoin('his_department as execute_department', 'execute_department.id', '=', 'xa_v_his_service_req_list.execute_department_id')
            ->leftJoin('his_department as request_department', 'request_department.id', '=', 'xa_v_his_service_req_list.request_department_id')
            ->leftJoin('his_test_sample_type as test_sample_type', 'test_sample_type.id', '=', 'xa_v_his_service_req_list.test_sample_type_id')

            ->select([
                'xa_v_his_service_req_list.id',
                'xa_v_his_service_req_list.is_active',
                'xa_v_his_service_req_list.is_delete',
                'xa_v_his_service_req_list.is_no_execute',
                'xa_v_his_service_req_list.service_req_stt_code',
                'xa_v_his_service_req_list.service_req_stt_name',

                'xa_v_his_service_req_list.service_req_code',
                'xa_v_his_service_req_list.tdl_treatment_code',
                'xa_v_his_service_req_list.tdl_patient_code',
                'his_service_req_type.service_req_type_code',
                'his_service_req_type.service_req_type_name',
                'xa_v_his_service_req_list.tdl_patient_name',

                'xa_v_his_service_req_list.execute_room_id',
                'execute_room.room_code as execute_room_code',
                'execute_room.room_name as execute_room_name',
                'xa_v_his_service_req_list.request_room_id',
                'request_room.room_code as request_room_code',
                'request_room.room_name as request_room_name',

                'xa_v_his_service_req_list.is_main_exam',
                'xa_v_his_service_req_list.intruction_time',

                'xa_v_his_service_req_list.request_loginname',
                'xa_v_his_service_req_list.request_username',
                'xa_v_his_service_req_list.execute_loginname',
                'xa_v_his_service_req_list.execute_username',

                'ration_time.ration_time_code',
                'ration_time.ration_time_name',
                'xa_v_his_service_req_list.tdl_patient_dob',
                'xa_v_his_service_req_list.create_time',
                'xa_v_his_service_req_list.creator',
                'xa_v_his_service_req_list.modify_time',
                'xa_v_his_service_req_list.modifier',

                'xa_v_his_service_req_list.tdl_patient_gender_name',
                'execute_department.department_code as execute_department_code',
                'execute_department.department_name as execute_department_name',
                'xa_v_his_service_req_list.num_order',
                'xa_v_his_service_req_list.tdl_patient_id',

                'xa_v_his_service_req_list.is_sent_ext', // đã gửi yêu cầu
                'xa_v_his_service_req_list.barcode',
                'xa_v_his_service_req_list.session_code', // mã lượt chỉ định 
                'xa_v_his_service_req_list.use_time',
                'xa_v_his_service_req_list.remedy_count', // Số thang
                'xa_v_his_service_req_list.SAMPLER_LOGINNAME', // người lấy mẫu               
                'xa_v_his_service_req_list.SAMPLER_USERNAME',
                'xa_v_his_service_req_list.RECEIVE_SAMPLE_LOGINNAME', // người nhận mẫu                
                'xa_v_his_service_req_list.RECEIVE_SAMPLE_USERNAME',
                'test_sample_type.test_sample_type_code', // Loại mẫu
                'test_sample_type.test_sample_type_name',
            ]);
    }
    public function applyJoinsDanhSachChiDinhKhiThemToDieuTri()
    {
        return $this->serviceReqListVView
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'xa_v_his_service_req_list.service_req_type_id')
            ->leftJoin('v_his_room as request_room', 'request_room.id', '=', 'xa_v_his_service_req_list.request_room_id')

            ->select([
                'xa_v_his_service_req_list.id as key',

                'xa_v_his_service_req_list.id',
                'xa_v_his_service_req_list.is_active',
                'xa_v_his_service_req_list.is_delete',
                'xa_v_his_service_req_list.is_no_execute',
                'xa_v_his_service_req_list.service_req_stt_code',
                'xa_v_his_service_req_list.service_req_stt_name',

                'xa_v_his_service_req_list.service_req_code',
                'his_service_req_type.service_req_type_code',
                'his_service_req_type.service_req_type_name',

                'xa_v_his_service_req_list.intruction_time',
                'xa_v_his_service_req_list.intruction_date',

                'xa_v_his_service_req_list.request_room_id',
                'request_room.room_type_code as request_room_type_code',
                'request_room.room_type_name as request_room_type_name',

            ]);
    }
    public function applyJoinsThucHienDonDuTruKhiThemToDieuTri()
    {
        return $this->serviceReqListVView
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'xa_v_his_service_req_list.service_req_type_id')

            ->select([
                'xa_v_his_service_req_list.id as key',

                'xa_v_his_service_req_list.id',
                'xa_v_his_service_req_list.is_active',
                'xa_v_his_service_req_list.is_delete',
                'xa_v_his_service_req_list.is_no_execute',
                'xa_v_his_service_req_list.service_req_stt_code',
                'xa_v_his_service_req_list.service_req_stt_name',

                'xa_v_his_service_req_list.service_req_code',
                'his_service_req_type.service_req_type_code',
                'his_service_req_type.service_req_type_name',

                'xa_v_his_service_req_list.intruction_time',
                'xa_v_his_service_req_list.intruction_date',

                'xa_v_his_service_req_list.request_room_id',

            ]);
    }
    public function applyWithParam($query)
    {
        return $query->with([
            'sere_serv:id,service_req_id,service_id,tdl_service_code,tdl_service_name,amount,patient_type_id,exp_mest_medicine_id',
            'sere_serv.service:id,service_code,service_name,service_unit_id',
            'sere_serv.service.service_unit:id,service_unit_code,service_unit_name',
            'sere_serv.patient_type:id,patient_type_code,patient_type_name',
            'sere_serv.exp_mest_medicine:id,tutorial',
        ]);
    }
    public function applyWithParamChiDinh($query)
    {
        return $query->with([
            'danh_sach_dich_vu_chi_dinh',
            'danh_sach_don',
            'the_kcb_thong_minh',
            'don_xuat',
        ]);
    }
    public function applyWithParamDanhSachChiDinhKhiThemToDieuTri($query)
    {
        return $query->with([
            'danh_sach_dich_vu_chi_dinh',
            'danh_sach_don',
        ]);
    }
    public function applyWithParamThucHienDonDuTruKhiThemToDieuTri($query)
    {
        return $query->with([
            'danh_sach_dich_vu_chi_dinh',
            'danh_sach_don',
        ]);
    }

    public function applyKeywordFilter($query, $keyword)
    {
        if ($keyword != null) {
            return $query->where(function ($query) use ($keyword) {
                $query->whereRaw("
                REGEXP_LIKE(
                    NLSSORT(xa_v_his_service_req_list.tdl_patient_name, 'NLS_SORT=GENERIC_M_AI'),
                    NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                    'i'
                )
            ", [$keyword])
                    ->orWhere(('xa_v_his_service_req_list.tdl_patient_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('xa_v_his_service_req_list.tdl_treatment_code'), 'like', '%' . $keyword . '%')
                    ->orWhere(('xa_v_his_service_req_list.service_req_code'), 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('xa_v_his_service_req_list.is_active'), $isActive);
        }
        return $query;
    }
    public function applyTrackingIdIsNullFilter($query)
    {
        $query->whereNull(('xa_v_his_service_req_list.tracking_id'));
        return $query;
    }
    public function applyUsedForTrackingIdIsNullFilter($query)
    {
        $query->whereNull(('xa_v_his_service_req_list.used_for_tracking_id'));
        return $query;
    }
    public function applyIsDonTuTrucFilter($query)
    {
        $query->where(('his_service_req_type.service_req_type_code'), 'DT');
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('xa_v_his_service_req_list.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyPatientIdFilter($query, $param)
    {
        $query->where(('xa_v_his_service_req_list.tdl_patient_id'), $param);
        return $query;
    }
    public function applyIsNoExecuteFilter($query)
    {
        $query->where(function ($q) {
            $q->where('xa_v_his_service_req_list.IS_NO_EXECUTE', 0)
                ->orWhereNull('xa_v_his_service_req_list.IS_NO_EXECUTE');
        });
        return $query;
    }
    public function applyTrackingIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('xa_v_his_service_req_list.tracking_id'), $param);
        }
        return $query;
    }
    public function applyServiceReqIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereIn(('xa_v_his_service_req_list.id'), $param);
        }
        return $query;
    }
    public function applyTreatmentIdFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(('xa_v_his_service_req_list.treatment_id'), $param);
        }
        return $query;
    }
    public function applyPatientCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('xa_v_his_service_req_list.tdl_patient_code'), $param);
        }
        return $query;
    }
    public function applyServiceReqCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('xa_v_his_service_req_list.service_req_code'), $param);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('xa_v_his_service_req_list.intruction_time'), '>=', $param);
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('xa_v_his_service_req_list.intruction_time'), '<=', $param);
        }
        return $query;
    }
    public function applyUseTimeToFilter($query, $param)
    {
        if ($param != null) {
            $query->where(function ($q) use ($param) {
                $q->where('xa_v_his_service_req_list.use_time_to', '<=', $param)
                    ->orWhereNull('xa_v_his_service_req_list.use_time_to');
            });
        }
        return $query;
    }
    public function applyUseTimeFromFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('xa_v_his_service_req_list.use_time'), '>=', $param);
        }
        return $query;
    }

    public function applyExecuteRoomIdFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('xa_v_his_service_req_list.execute_room_id'), $param);
        }
        return $query;
    }
    public function applyRequestLoginnameFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('xa_v_his_service_req_list.request_loginname'), $param);
        }
        return $query;
    }
    public function applyServiceReqTypeIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereIn(('xa_v_his_service_req_list.service_req_type_id'), $param);
        }
        return $query;
    }
    public function applyServiceReqSttIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereIn(('xa_v_his_service_req_list.service_req_stt_id'), $param);
        }
        return $query;
    }
    public function applyStoreCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->join('his_treatment', 'his_treatment.id', '=', 'xa_v_his_service_req_list.treatment_id')
                ->where(('his_treatment.store_code'), $param);
        }
        return $query;
    }
    public function applyServiceReqSttNotInChuaThucHienFilter($query)
    {
        $query->whereNotIn(('xa_v_his_service_req_list.service_req_stt_code'), ['01']);
        return $query;
    }
    public function applyTypeFilter($query, $param, $currentRoomId, $currentLoginname)
    {
        $this->room = new Room();
        $departmentId = $this->room->find($currentRoomId)->department_id ?? 0;
        switch ($param) {
            case 'tatCa':
                return $query;
            case 'toiTao':
                return $query->where(('xa_v_his_service_req_list.creator'), $currentLoginname);
            case 'phongChiDinh':
                return $query->where(('xa_v_his_service_req_list.request_room_id'), $currentRoomId);
            case 'khoaChiDinh':
                return $query->where(('xa_v_his_service_req_list.request_department_id'), $departmentId);
            case 'trongKhoa':
                return $query->where(('xa_v_his_service_req_list.request_department_id'), $departmentId);
            case 'khoaThucHien':
                return $query->where(('xa_v_his_service_req_list.execute_department_id'), $departmentId);
            default:
                return $query;
        }
    }
    public function applyToiChiDinhFilter($query, $param, $currentLoginname)
    {
        if ($param) {
            return $query->where(('xa_v_his_service_req_list.request_loginname'), $currentLoginname);
        }
        return $query;
    }
    public function applyGroupByField($data, $groupByFields = [])
    {
        if (empty($groupByFields)) {
            return $data;
        }

        // Chuyển các field thành snake_case trước khi nhóm
        $fieldMappings = [];
        foreach ($groupByFields as $field) {
            $snakeField = Str::snake($field);
            $fieldMappings[$snakeField] = $field;
        }

        $snakeFields = array_keys($fieldMappings);

        // Đệ quy nhóm dữ liệu theo thứ tự fields đã convert
        $groupData = function ($items, $fields) use (&$groupData, $fieldMappings) {
            if (empty($fields)) {
                return $items->values(); // Hết field nhóm -> Trả về danh sách gốc
            }

            $currentField = array_shift($fields);
            $originalField = $fieldMappings[$currentField];

            return $items->groupBy(function ($item) use ($currentField) {
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField, $currentField) {
                $result =  [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                ];
                switch ($currentField) {
                    case 'intruction_time':
                        $firstItem = $group->first();
                        $result['intructionDate'] = $firstItem['intruction_date'] ?? null;
                        break;
                    default:
                }
                $result['children'] = $groupData($group, $fields);
                return $result;
            })->values();
        };

        return $groupData(collect($data), $snakeFields);
    }


    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('xa_v_his_service_req_list.' . $key, $item);
                }
            }
        }

        return $query;
    }

    public function applyOrderingUnionAll($query, $orderBy)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                $query->orderBy($key, $item);
            }
        }

        return $query;
    }
    function addThongTinDon($data)
    {
        foreach ($data as &$item) {
            $firstChiTiet = $item['chi_tiet_don'][0] ?? null;

            $item['expMestCode'] = $firstChiTiet['exp_mest_code'] ?? null;
            $item['expMestMediStockCode'] = $firstChiTiet['exp_mest_medi_stock_code'] ?? null;
            $item['expMestMediStockName'] = $firstChiTiet['exp_mest_medi_stock_name'] ?? null;
        }

        return $data;
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
    public function applyUnionAllDichVuDon($query)
    {
        $queryDichVu = clone $query;
        $queryDon = clone $query;

        $queryDichVu->leftJoin('his_sere_serv sere_serv', 'sere_serv.service_req_id', '=', 'xa_v_his_service_req_list.id')
            ->leftJoin('his_service_type service_type', 'service_type.id', '=', 'sere_serv.tdl_service_type_id')
            ->leftJoin('his_service_unit service_unit', 'service_unit.id', '=', 'sere_serv.tdl_service_unit_id')
            ->addSelect([
                'sere_serv.tdl_service_name',
                'sere_serv.tdl_service_code',
                'sere_serv.amount',
                'service_unit.service_unit_code',
                'service_unit.service_unit_name',
                'service_type.service_type_code',
                'service_type.service_type_name',
                DB::connection('oracle_his')->raw("NULL as sort_num_order"),

            ])
            ->whereNotIn('service_type.service_type_code', ['TH', 'VT']); // thuốc và vật tư lấy ở dưới rồi hợp lại

        $queryDon->leftJoin('xa_v_his_don don', 'don.service_req_id', '=', 'xa_v_his_service_req_list.id')
            ->leftJoin('his_service service', 'service.id', '=', 'don.service_id')
            ->leftJoin('his_service_unit service_unit', 'service_unit.id', '=', 'service.service_unit_id')
            ->leftJoin('his_service_type service_type', 'service_type.id', '=', 'service.service_type_id')
            ->addSelect([
                'service.service_name as tdl_service_name',
                'service.service_code as tdl_service_code',
                'don.amount',
                'service_unit.service_unit_code',
                'service_unit.service_unit_name',
                'service_type.service_type_code',
                'service_type.service_type_name',
                'don.num_order as sort_num_order',
            ])
            ->where('don.is_delete', 0);

        $queryResult =  $queryDichVu->unionall($queryDon); // Hợp đơn với dịch vụ ở trên

        return $queryResult;
    }
    public function getById($id)
    {
        return $this->serviceReqListVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->serviceReqListVView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'service_req_list_v_view_code' => $request->service_req_list_v_view_code,
    //         'service_req_list_v_view_name' => $request->service_req_list_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'service_req_list_v_view_code' => $request->service_req_list_v_view_code,
    //         'service_req_list_v_view_name' => $request->service_req_list_v_view_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('id');
            $maxId = $this->applyJoins()->max('id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('service_req_list_v_view', 'v_his_service_req_list', $startId, $endId, $batchSize);
            }
        }
    }
}
