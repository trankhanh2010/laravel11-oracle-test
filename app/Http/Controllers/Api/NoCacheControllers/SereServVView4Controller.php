<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServVView4DTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServVView4\CreateSereServVView4Request;
use App\Http\Requests\SereServVView4\UpdateSereServVView4Request;
use App\Models\View\SereServVView4;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServVView4Service;
use Illuminate\Http\Request;


class SereServVView4Controller extends BaseApiCacheController
{
    protected $sereServVView4Service;
    protected $sereServVView4DTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SereServVView4Service $sereServVView4Service, SereServVView4 $sereServVView4)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->sereServVView4Service = $sereServVView4Service;
        $this->sereServVView4 = $sereServVView4;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServVView4, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServVView4DTO = new SereServVView4DTO(
            $this->sereServVView4Name,
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
        );
        $this->sereServVView4Service->withParams($this->sereServVView4DTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->sereServVView4Name);
            } else {
                $data = $this->sereServVView4Service->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->sereServVView4Name);
            } else {
                $data = $this->sereServVView4Service->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServVView4, $this->sereServVView4Name);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServVView4Name, $id);
        } else {
            $data = $this->sereServVView4Service->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
