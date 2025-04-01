<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\EthnicDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Ethnic\CreateEthnicRequest;
use App\Http\Requests\Ethnic\UpdateEthnicRequest;
use App\Models\SDA\Ethnic;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\EthnicService;
use Illuminate\Http\Request;


class EthnicController extends BaseApiCacheController
{
    protected $ethnicService;
    protected $ethnicDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, EthnicService $ethnicService, Ethnic $ethnic)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ethnicService = $ethnicService;
        $this->ethnic = $ethnic;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->ethnic);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ethnicDTO = new EthnicDTO(
            $this->ethnicName,
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
        $this->ethnicService->withParams($this->ethnicDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ethnicName);
            } else {
                $data = $this->ethnicService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->ethnicName);
            } else {
                $data = $this->ethnicService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ethnic, $this->ethnicName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ethnicName, $id);
        } else {
            $data = $this->ethnicService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateEthnicRequest $request)
    {
        return $this->ethnicService->createEthnic($request);
    }
    public function update(UpdateEthnicRequest $request, $id)
    {
        return $this->ethnicService->updateEthnic($id, $request);
    }
    public function destroy($id)
    {
        return $this->ethnicService->deleteEthnic($id);
    }
}
