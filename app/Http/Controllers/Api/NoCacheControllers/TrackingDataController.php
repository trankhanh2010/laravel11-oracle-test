<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TrackingDataDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TrackingData\CreateTrackingDataRequest;
use App\Http\Requests\TrackingData\UpdateTrackingDataRequest;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TrackingDataService;
use Illuminate\Http\Request;


class TrackingDataController extends BaseApiCacheController
{
    protected $trackingDataService;
    protected $trackingDataDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TrackingDataService $trackingDataService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->trackingDataService = $trackingDataService;
        // Kiểm tra tên trường trong bảng
        // if ($this->orderBy != null) {
        //     $this->orderByJoin = [
        //     ];
        //     $columns = $this->getColumnsTable($this->trackingData);
        //     $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        // }
        // Thêm tham số vào service
        $this->trackingDataDTO = new TrackingDataDTO(
            $this->trackingDataName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
            $this->orderBy,
            $this->orderByJoin,
            $this->orderByString,
            $this->getAll,
            $this->start,
            $this->limit,
            $request,
            $this->appCreator, 
            $this->appModifier, 
            $this->time,
            $this->treatmentId,
            $this->relations,
            $this->param,
        );
        $this->trackingDataService->withParams($this->trackingDataDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->trackingDataService->handleDataBaseGetAll();
        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->isActiveName => $this->isActive,
            $this->keywordName => $this->keyword,
            $this->orderByName => $this->orderByRequest
        ];
        return returnDataSuccess($paramReturn, $data['data']);
    }

    // public function show($id)
    // {
    //     if ($this->checkParam()) {
    //         return $this->checkParam();
    //     }
    //     if ($id !== null) {
    //         $validationError = $this->validateAndCheckId($id, $this->trackingData, $this->trackingDataName);
    //         if ($validationError) {
    //             return $validationError;
    //         }
    //     }
    //     if ($this->elastic) {
    //         $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->trackingDataName, $id);
    //     } else {
    //         $data = $this->trackingDataService->handleDataBaseGetWithId($id);
    //     }
    //     $paramReturn = [
    //         $this->idName => $id,
    //         $this->isActiveName => $this->isActive,
    //     ];
    //     return returnDataSuccess($paramReturn, $data);
    // }
}
