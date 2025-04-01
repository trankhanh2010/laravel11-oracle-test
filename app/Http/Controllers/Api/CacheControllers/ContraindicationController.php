<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ContraindicationDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Contraindication\CreateContraindicationRequest;
use App\Http\Requests\Contraindication\UpdateContraindicationRequest;
use App\Models\HIS\Contraindication;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ContraindicationService;
use Illuminate\Http\Request;


class ContraindicationController extends BaseApiCacheController
{
    protected $contraindicationService;
    protected $contraindicationDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ContraindicationService $contraindicationService, Contraindication $contraindication)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->contraindicationService = $contraindicationService;
        $this->contraindication = $contraindication;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->contraindication);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->contraindicationDTO = new ContraindicationDTO(
            $this->contraindicationName,
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
        $this->contraindicationService->withParams($this->contraindicationDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->contraindicationName);
            } else {
                $data = $this->contraindicationService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->contraindicationName);
            } else {
                $data = $this->contraindicationService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->contraindication, $this->contraindicationName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->contraindicationName, $id);
        } else {
            $data = $this->contraindicationService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateContraindicationRequest $request)
    {
        return $this->contraindicationService->createContraindication($request);
    }
    public function update(UpdateContraindicationRequest $request, $id)
    {
        return $this->contraindicationService->updateContraindication($id, $request);
    }
    public function destroy($id)
    {
        return $this->contraindicationService->deleteContraindication($id);
    }
}
