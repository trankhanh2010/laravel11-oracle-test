<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\CommuneDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Commune\CreateCommuneRequest;
use App\Http\Requests\Commune\UpdateCommuneRequest;
use App\Models\SDA\Commune;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\CommuneService;
use Illuminate\Http\Request;


class CommuneController extends BaseApiCacheController
{
    protected $communeService;
    protected $communeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, CommuneService $communeService, Commune $commune)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->communeService = $communeService;
        $this->commune = $commune;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'district_code',
                'district_name',
            ];
            $columns = $this->getColumnsTable($this->commune);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->communeDTO = new CommuneDTO(
            $this->communeName,
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
        );
        $this->communeService->withParams($this->communeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->communeName);
            } else {
                $data = $this->communeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->communeName);
            } else {
                $data = $this->communeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->commune, $this->communeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->communeName, $id);
        } else {
            $data = $this->communeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateCommuneRequest $request)
    {
        return $this->communeService->createCommune($request);
    }
    public function update(UpdateCommuneRequest $request, $id)
    {
        return $this->communeService->updateCommune($id, $request);
    }
    public function destroy($id)
    {
        return $this->communeService->deleteCommune($id);
    }
}
