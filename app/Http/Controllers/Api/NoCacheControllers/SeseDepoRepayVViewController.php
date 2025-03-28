<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SeseDepoRepayVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SeseDepoRepayVView\CreateSeseDepoRepayVViewRequest;
use App\Http\Requests\SeseDepoRepayVView\UpdateSeseDepoRepayVViewRequest;
use App\Models\View\SeseDepoRepayVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SeseDepoRepayVViewService;
use Illuminate\Http\Request;


class SeseDepoRepayVViewController extends BaseApiCacheController
{
    protected $seseDepoRepayVViewService;
    protected $seseDepoRepayVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SeseDepoRepayVViewService $seseDepoRepayVViewService, SeseDepoRepayVView $seseDepoRepayVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->seseDepoRepayVViewService = $seseDepoRepayVViewService;
        $this->seseDepoRepayVView = $seseDepoRepayVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->seseDepoRepayVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->seseDepoRepayVViewDTO = new SeseDepoRepayVViewDTO(
            $this->seseDepoRepayVViewName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->tdlTreatmentId,
            $this->param,
        );
        $this->seseDepoRepayVViewService->withParams($this->seseDepoRepayVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->seseDepoRepayVViewName);
            } else {
                $data = $this->seseDepoRepayVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->seseDepoRepayVViewName);
            } else {
                $data = $this->seseDepoRepayVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->seseDepoRepayVView, $this->seseDepoRepayVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->seseDepoRepayVViewName, $id);
        } else {
            $data = $this->seseDepoRepayVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
