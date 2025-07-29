<?php

namespace App\Services\Model;

use App\DTOs\SereServListVViewDTO;
use App\DTOs\TrackingListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TrackingListVView\InsertTrackingListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Models\View\TrackingListVView;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrackingListVViewRepository;

class TrackingListVViewService
{
    protected $trackingListVViewRepository;
    protected $sereServListVViewService;
    protected $params;
    public function __construct(
        TrackingListVViewRepository $trackingListVViewRepository,
        SereServListVViewService $sereServListVViewService,
        )
    {
        $this->trackingListVViewRepository = $trackingListVViewRepository;
        $this->sereServListVViewService = $sereServListVViewService;
    }
    public function withParams(TrackingListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->trackingListVViewRepository->applyJoins();
            $data = $this->trackingListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->trackingListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->trackingListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->trackingListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->trackingListVViewRepository->applyJoins();
        $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $count = $data->count();
        $data = $this->trackingListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->trackingListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->trackingListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseDanhSachToDieuTriCu()
    {
        $data = $this->trackingListVViewRepository->applyJoinsDanhSachToDieuTriCu();
        $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $count = $data->count();
        $data = $this->trackingListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->trackingListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->trackingListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseDanhSachTheoKhoaDieuTri()
    {
        $data = $this->trackingListVViewRepository->applyJoinsDanhSachTheoKhoaDieuTri();
        $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $count = null;
        $data = $this->trackingListVViewRepository->applyOrdering($data, $this->params->orderBy, []);
        $data = $this->trackingListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $this->params->groupBy = ['departmentName', 'intructionDate'];
        $data = $this->trackingListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        // Lấy và thêm những dịch vụ không có tờ điều trị vào danh sách tờ điều trị (id sẽ là null và có thêm phần children chứa những dịch vụ ở trong), được nhóm theo khoa và ngày
        $dataNotInTracking = $this->getSereServNotInTracking($this->params->treatmentId);

        $data = $this->addNotInTracking($data, $dataNotInTracking);
        return ['data' => $data, 'count' => $count];
    }
    public function getSereServNotInTracking($treatmentId){
        $params = new SereServListVViewDTO('','','','','','','','','','','','','','',$treatmentId,'','','','','','','','','','','',);
        $this->sereServListVViewService->withParams($params);
        return $this->sereServListVViewService->getAllDataFromDatabaseNotInTracking();
    }
    public function addNotInTracking($data, $dataNotInTracking)
    {
        if(empty($dataNotInTracking) || empty($dataNotInTracking['data'])){
            return $data;
        }
        // Đảm bảo $data là mảng
        $data =  $this->deepToArray($data);
        $dataNotInTracking = is_array($dataNotInTracking['data']) ? $dataNotInTracking['data'] : $dataNotInTracking['data']->toArray();
        foreach ($dataNotInTracking as $key => $item) {
            $deptName = $item['requestDepartmentName'];
            $intructionDate = $item['intructionDate'];

            // Tìm vị trí của departmentName trong $data
            $deptIndex = collect($data)->search(function ($d) use ($deptName) {
                return $d['departmentName'] === $deptName;
            });

            // Nếu không tìm thấy, tạo mới
            if ($deptIndex === false) {
                $data[] = [
                    'key' => $deptName,
                    'departmentName' => $deptName,
                    'total' => 0,
                    'children' => [
                        [
                            'key' => $deptName.$intructionDate,
                            'intructionDate' => $intructionDate,
                            'total' => 0,
                            'children' => [],
                        ],
                    ]
                ];
                end($data);
                $deptIndex = array_key_last($data);
            }
            // Tìm vị trí của intructionDate trong data của $data[$deptIndex]['children']
            $intructionDateIndex = collect($data[$deptIndex]['children'])->search(function ($d) use ($intructionDate) {
                return $d['intructionDate'] === $intructionDate;
            });

            // Nếu không tìm thấy, tạo mới
            if ($intructionDateIndex === false) {
                $data[$deptIndex]['children'][] = [
                    'key' => $deptName.$intructionDate,
                    'intructionDate' => $intructionDate,
                    'total' => 0,
                    'children' => [
                        [
                            'key' => $deptName.$intructionDate.'_not_in_tracking',
                            'id' => null,
                            'creator' => null,
                            'trackingTime' => null,
                            'icdCode' => null,
                            'icdName' => null,
                            'content' => null,
                            'departmentName' => $deptName,
                            'intructionDate' => $intructionDate,
                            'trackingCreator' => null,
                            'services' => [], // chứa danh sách dịch vụ
                        ]
                    ],
                ];
                end($data[$deptIndex]['children']);
                $intructionDateIndex = array_key_last($data[$deptIndex]['children']);
            }
            // Tham chiếu trực tiếp tới group children của intructionDate
            $children = &$data[$deptIndex]['children'][$intructionDateIndex]['children'];

            // Tìm bản ghi children instructionDate có id = null
            $childIndex = collect($children)->search(function ($c) use($intructionDate) {
                return empty($c['id']);
            });

            $isFound = $childIndex !== false;

            // Nếu chưa có, tạo mới mảng chứa list dịch vụ
            if (!$isFound) {
                $children[] = [
                    'key' => $deptName.$intructionDate.'_not_in_tracking',
                    'id' => null,
                    'creator' => null,
                    'trackingTime' => null,
                    'icdCode' => null,
                    'icdName' => null,
                    'content' => null,
                    'departmentName' => $deptName,
                    'intructionDate' => $intructionDate,
                    'trackingCreator' => null,
                    'services' => [], // chứa danh sách dịch vụ
                ];

                $children = is_array($children) ? $children : $children->toArray();
                $childIndex = array_key_last($children);

            }
            // Thêm item này vào danh sách services trong nhóm tương ứng
            if (isset($children[$childIndex]) && is_array($children[$childIndex])) {
                $children[$childIndex]['services'] = $children[$childIndex]['services'] ?? [];
                $children[$childIndex]['services'][] = is_array($item) ? $item : $item->toArray();
            }
        }

        return $data;
    }
    public function deepToArray($data)
    {
        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->toArray();
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->deepToArray($value);
            }
        }

        return $data;
    }

    private function getDataById($id)
    {
        $data = $this->trackingListVViewRepository->applyJoins()
        ->where('id', $id);
        $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
    $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAllDanhSachToDieuTriCu()
    {
        try {
            return $this->getAllDataFromDatabaseDanhSachToDieuTriCu();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAllDanhSachTheoKhoaDieuTri()
    {
        try {
            return $this->getAllDataFromDatabaseDanhSachTheoKhoaDieuTri();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }

    // public function createTrackingListVView($request)
    // {
    //     try {
    //         $data = $this->trackingListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTrackingListVViewIndex($data, $this->params->trackingListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
    //     }
    // }

    // public function updateTrackingListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->trackingListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->trackingListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTrackingListVViewIndex($data, $this->params->trackingListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
    //     }
    // }

    // public function deleteTrackingListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->trackingListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->trackingListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->trackingListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
    //     }
    // }
}
