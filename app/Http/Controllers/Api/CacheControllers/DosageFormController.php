<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DosageFormDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DosageForm\CreateDosageFormRequest;
use App\Http\Requests\DosageForm\UpdateDosageFormRequest;
use App\Models\HIS\DosageForm;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DosageFormService;
use Illuminate\Http\Request;


class DosageFormController extends BaseApiCacheController
{
    protected $dosageFormService;
    protected $dosageFormDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DosageFormService $dosageFormService, DosageForm $dosageForm)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->dosageFormService = $dosageFormService;
        $this->dosageForm = $dosageForm;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->dosageForm);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->dosageFormDTO = new DosageFormDTO(
            $this->dosageFormName,
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
        $this->dosageFormService->withParams($this->dosageFormDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->dosageFormName);
            } else {
                $data = $this->dosageFormService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->dosageFormName);
            } else {
                $data = $this->dosageFormService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->dosageForm, $this->dosageFormName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->dosageFormName, $id);
        } else {
            $data = $this->dosageFormService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateDosageFormRequest $request)
    {
        return $this->dosageFormService->createDosageForm($request);
    }
    public function update(UpdateDosageFormRequest $request, $id)
    {
        return $this->dosageFormService->updateDosageForm($id, $request);
    }
    public function destroy($id)
    {
        return $this->dosageFormService->deleteDosageForm($id);
    }
}
