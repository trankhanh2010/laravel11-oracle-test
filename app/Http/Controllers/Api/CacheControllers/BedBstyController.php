<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BedBstyDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BedBsty\CreateBedBstyRequest;
use App\Http\Requests\BedBsty\UpdateBedBstyRequest;
use App\Models\HIS\BedBsty;
use App\Repositories\BedBstyRepository;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BedBstyService;
use Illuminate\Http\Request;


class BedBstyController extends BaseApiCacheController
{
    protected $bedBstyService;
    protected $bedBstyDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BedBstyService $bedBstyService, BedBsty $bedBsty)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bedBstyService = $bedBstyService;
        $this->bedBsty = $bedBsty;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'service_name',
                'service_code',
                'service_type_name',
                'service_type_code',
                'bed_name',
                'bed_code',
                'bed_room_name',
                'bed_room_code',
                'department_name',
                'department_code'
            ];
            $columns = $this->getColumnsTable($this->bedBsty);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bedBstyDTO = new BedBstyDTO(
            $this->bedBstyName,
            $this->keyword,
            $this->isActive,
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
            $this->serviceIds,
            $this->bedIds,
            $this->param,
            $this->noCache,
        );
        $this->bedBstyService->withParams($this->bedBstyDTO);

    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bedBstyName);
            } else {
                $data = $this->bedBstyService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bedBstyName);
            } else {
                $data = $this->bedBstyService->handleDataBaseGetAll();
            }
        }
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

    public function show($id)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($id !== null) {
            $validationError = $this->validateAndCheckId($id, $this->bedBsty, $this->bedBstyName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bedBstyName, $id);
        } else {
            $data = $this->bedBstyService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBedBstyRequest $request)
    {
        return $this->bedBstyService->createBedBsty($request);
    }
}
