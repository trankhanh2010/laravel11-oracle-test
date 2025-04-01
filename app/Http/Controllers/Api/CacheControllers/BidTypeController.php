<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BidTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BidType\CreateBidTypeRequest;
use App\Http\Requests\BidType\UpdateBidTypeRequest;
use App\Models\HIS\BidType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BidTypeService;
use Illuminate\Http\Request;


class BidTypeController extends BaseApiCacheController
{
    protected $bidTypeService;
    protected $bidTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BidTypeService $bidTypeService, BidType $bidType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bidTypeService = $bidTypeService;
        $this->bidType = $bidType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bidType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bidTypeDTO = new BidTypeDTO(
            $this->bidTypeName,
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
            $this->param,
            $this->noCache,
        );
        $this->bidTypeService->withParams($this->bidTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bidTypeName);
            } else {
                $data = $this->bidTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bidTypeName);
            } else {
                $data = $this->bidTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bidType, $this->bidTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bidTypeName, $id);
        } else {
            $data = $this->bidTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBidTypeRequest $request)
    {
        return $this->bidTypeService->createBidType($request);
    }
    public function update(UpdateBidTypeRequest $request, $id)
    {
        return $this->bidTypeService->updateBidType($id, $request);
    }
    public function destroy($id)
    {
        return $this->bidTypeService->deleteBidType($id);
    }
}
