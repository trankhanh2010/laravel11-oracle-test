<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ManufacturerDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Manufacturer\CreateManufacturerRequest;
use App\Http\Requests\Manufacturer\UpdateManufacturerRequest;
use App\Models\HIS\Manufacturer;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ManufacturerService;
use Illuminate\Http\Request;


class ManufacturerController extends BaseApiCacheController
{
    protected $manufacturerService;
    protected $manufacturerDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ManufacturerService $manufacturerService, Manufacturer $manufacturer)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->manufacturerService = $manufacturerService;
        $this->manufacturer = $manufacturer;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->manufacturer);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->manufacturerDTO = new ManufacturerDTO(
            $this->manufacturerName,
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
        $this->manufacturerService->withParams($this->manufacturerDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->manufacturerName);
            } else {
                $data = $this->manufacturerService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->manufacturerName);
            } else {
                $data = $this->manufacturerService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->manufacturer, $this->manufacturerName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->manufacturerName, $id);
        } else {
            $data = $this->manufacturerService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateManufacturerRequest $request)
    {
        return $this->manufacturerService->createManufacturer($request);
    }
    public function update(UpdateManufacturerRequest $request, $id)
    {
        return $this->manufacturerService->updateManufacturer($id, $request);
    }
    public function destroy($id)
    {
        return $this->manufacturerService->deleteManufacturer($id);
    }
}
