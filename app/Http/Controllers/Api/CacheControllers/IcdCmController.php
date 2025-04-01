<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\IcdCmDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\IcdCm\CreateIcdCmRequest;
use App\Http\Requests\IcdCm\UpdateIcdCmRequest;
use App\Models\HIS\IcdCm;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\IcdCmService;
use Illuminate\Http\Request;


class IcdCmController extends BaseApiCacheController
{
    protected $icdCmService;
    protected $icdCmDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, IcdCmService $icdCmService, IcdCm $icdCm)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->icdCmService = $icdCmService;
        $this->icdCm = $icdCm;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->icdCm);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->icdCmDTO = new IcdCmDTO(
            $this->icdCmName,
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
        $this->icdCmService->withParams($this->icdCmDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->icdCmName);
            } else {
                $data = $this->icdCmService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->icdCmName);
            } else {
                $data = $this->icdCmService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->icdCm, $this->icdCmName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->icdCmName, $id);
        } else {
            $data = $this->icdCmService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateIcdCmRequest $request)
    {
        return $this->icdCmService->createIcdCm($request);
    }
    public function update(UpdateIcdCmRequest $request, $id)
    {
        return $this->icdCmService->updateIcdCm($id, $request);
    }
    public function destroy($id)
    {
        return $this->icdCmService->deleteIcdCm($id);
    }
}
