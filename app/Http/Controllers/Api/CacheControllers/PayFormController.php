<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PayFormDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PayForm\CreatePayFormRequest;
use App\Http\Requests\PayForm\UpdatePayFormRequest;
use App\Models\HIS\PayForm;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PayFormService;
use Illuminate\Http\Request;


class PayFormController extends BaseApiCacheController
{
    protected $payFormService;
    protected $payFormDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PayFormService $payFormService, PayForm $payForm)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->payFormService = $payFormService;
        $this->payForm = $payForm;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->payForm);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->payFormDTO = new PayFormDTO(
            $this->payFormName,
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
        $this->payFormService->withParams($this->payFormDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->payFormName);
            } else {
                $data = $this->payFormService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->payFormName);
            } else {
                $data = $this->payFormService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->payForm, $this->payFormName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->payFormName, $id);
        } else {
            $data = $this->payFormService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePayFormRequest $request)
    {
        return $this->payFormService->createPayForm($request);
    }
    public function update(UpdatePayFormRequest $request, $id)
    {
        return $this->payFormService->updatePayForm($id, $request);
    }
    public function destroy($id)
    {
        return $this->payFormService->deletePayForm($id);
    }
}
