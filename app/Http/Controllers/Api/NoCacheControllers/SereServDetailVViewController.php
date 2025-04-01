<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServDetailVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServDetailVView\CreateSereServDetailVViewRequest;
use App\Http\Requests\SereServDetailVView\UpdateSereServDetailVViewRequest;
use App\Models\View\SereServDetailVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServDetailVViewService;
use Illuminate\Http\Request;


class SereServDetailVViewController extends BaseApiCacheController
{
    protected $sereServDetailVViewService;
    protected $sereServDetailVViewDTO;
    public function __construct(Request $request, SereServDetailVViewService $sereServDetailVViewService, SereServDetailVView $sereServDetailVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sereServDetailVViewService = $sereServDetailVViewService;
        $this->sereServDetailVView = $sereServDetailVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServDetailVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServDetailVViewDTO = new SereServDetailVViewDTO(
            $this->sereServDetailVViewName,
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
            $this->serviceTypeCode,
            $this->param,
            $this->noCache,
        );
        $this->sereServDetailVViewService->withParams($this->sereServDetailVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;

        $data = $this->sereServDetailVViewService->handleDataBaseSearch();
           
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
            $validationError = $this->validateAndCheckId($id, $this->sereServDetailVView, $this->sereServDetailVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServDetailVViewName, $id);
        } else {
            $data = $this->sereServDetailVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
