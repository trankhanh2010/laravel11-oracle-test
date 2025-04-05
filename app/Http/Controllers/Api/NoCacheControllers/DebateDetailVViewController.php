<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\DebateDetailVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DebateDetailVView\CreateDebateDetailVViewRequest;
use App\Http\Requests\DebateDetailVView\UpdateDebateDetailVViewRequest;
use App\Models\View\DebateDetailVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DebateDetailVViewService;
use Illuminate\Http\Request;


class DebateDetailVViewController extends BaseApiCacheController
{
    protected $debateDetailVViewService;
    protected $debateDetailVViewDTO;
    public function __construct(Request $request, DebateDetailVViewService $debateDetailVViewService, DebateDetailVView $debateDetailVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->debateDetailVViewService = $debateDetailVViewService;
        $this->debateDetailVView = $debateDetailVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->debateDetailVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->debateDetailVViewDTO = new DebateDetailVViewDTO(
            $this->debateDetailVViewName,
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
            $this->param,
            $this->noCache,
        );
        $this->debateDetailVViewService->withParams($this->debateDetailVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->debateDetailVViewService->handleDataBaseGetAll();
        
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
            $validationError = $this->validateAndCheckId($id, $this->debateDetailVView, $this->debateDetailVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->debateDetailVViewName, $id);
        } else {
            $data = $this->debateDetailVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
