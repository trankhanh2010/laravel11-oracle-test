<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TranPatiFormDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TranPatiForm\CreateTranPatiFormRequest;
use App\Http\Requests\TranPatiForm\UpdateTranPatiFormRequest;
use App\Models\HIS\TranPatiForm;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TranPatiFormService;
use Illuminate\Http\Request;


class TranPatiFormController extends BaseApiCacheController
{
    protected $tranPatiFormService;
    protected $tranPatiFormDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TranPatiFormService $tranPatiFormService, TranPatiForm $tranPatiForm)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->tranPatiFormService = $tranPatiFormService;
        $this->tranPatiForm = $tranPatiForm;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->tranPatiForm);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->tranPatiFormDTO = new TranPatiFormDTO(
            $this->tranPatiFormName,
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
        $this->tranPatiFormService->withParams($this->tranPatiFormDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->tranPatiFormName);
            } else {
                $data = $this->tranPatiFormService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->tranPatiFormName);
            } else {
                $data = $this->tranPatiFormService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->tranPatiForm, $this->tranPatiFormName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->tranPatiFormName, $id);
        } else {
            $data = $this->tranPatiFormService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
