<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServSegrDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServSegr\CreateServSegrRequest;
use App\Http\Requests\ServSegr\UpdateServSegrRequest;
use App\Models\HIS\ServSegr;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServSegrService;
use Illuminate\Http\Request;


class ServSegrController extends BaseApiCacheController
{
    protected $servSegrService;
    protected $servSegrDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServSegrService $servSegrService, ServSegr $servSegr)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->servSegrService = $servSegrService;
        $this->servSegr = $servSegr;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'service_name',
                'service_code',
                'service_type_name',
                'service_type_code',
                'service_group_name',
                'service_group_code',
            ];
            $columns = $this->getColumnsTable($this->servSegr);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->servSegrDTO = new ServSegrDTO(
            $this->servSegrName,
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
        $this->servSegrService->withParams($this->servSegrDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->servSegrName);
            } else {
                $data = $this->servSegrService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->servSegrName);
            } else {
                $data = $this->servSegrService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->servSegr, $this->servSegrName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->servSegrName, $id);
        } else {
            $data = $this->servSegrService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
