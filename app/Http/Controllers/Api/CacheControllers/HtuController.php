<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\HtuDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Htu\CreateHtuRequest;
use App\Http\Requests\Htu\UpdateHtuRequest;
use App\Models\HIS\Htu;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\HtuService;
use Illuminate\Http\Request;


class HtuController extends BaseApiCacheController
{
    protected $htuService;
    protected $htuDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, HtuService $htuService, Htu $htu)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->htuService = $htuService;
        $this->htu = $htu;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->htu);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->htuDTO = new HtuDTO(
            $this->htuName,
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
        $this->htuService->withParams($this->htuDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->htuName);
            } else {
                $data = $this->htuService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->htuName);
            } else {
                $data = $this->htuService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->htu, $this->htuName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->htuName, $id);
        } else {
            $data = $this->htuService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateHtuRequest $request)
    {
        return $this->htuService->createHtu($request);
    }
    public function update(UpdateHtuRequest $request, $id)
    {
        return $this->htuService->updateHtu($id, $request);
    }
    public function destroy($id)
    {
        return $this->htuService->deleteHtu($id);
    }
}
