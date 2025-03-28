<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\UnlimitReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\UnlimitReason\CreateUnlimitReasonRequest;
use App\Http\Requests\UnlimitReason\UpdateUnlimitReasonRequest;
use App\Models\HIS\UnlimitReason;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\UnlimitReasonService;
use Illuminate\Http\Request;


class UnlimitReasonController extends BaseApiCacheController
{
    protected $unlimitReasonService;
    protected $unlimitReasonDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, UnlimitReasonService $unlimitReasonService, UnlimitReason $unlimitReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->unlimitReasonService = $unlimitReasonService;
        $this->unlimitReason = $unlimitReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->unlimitReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->unlimitReasonDTO = new UnlimitReasonDTO(
            $this->unlimitReasonName,
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
        );
        $this->unlimitReasonService->withParams($this->unlimitReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->unlimitReasonName);
            } else {
                $data = $this->unlimitReasonService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->unlimitReasonName);
            } else {
                $data = $this->unlimitReasonService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->unlimitReason, $this->unlimitReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->unlimitReasonName, $id);
        } else {
            $data = $this->unlimitReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateUnlimitReasonRequest $request)
    {
        return $this->unlimitReasonService->createUnlimitReason($request);
    }
    public function update(UpdateUnlimitReasonRequest $request, $id)
    {
        return $this->unlimitReasonService->updateUnlimitReason($id, $request);
    }
    public function destroy($id)
    {
        return $this->unlimitReasonService->deleteUnlimitReason($id);
    }
}
