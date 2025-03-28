<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\CareerTitleDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\CareerTitle\CreateCareerTitleRequest;
use App\Http\Requests\CareerTitle\UpdateCareerTitleRequest;
use App\Models\HIS\CareerTitle;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\CareerTitleService;
use Illuminate\Http\Request;


class CareerTitleController extends BaseApiCacheController
{
    protected $careerTitleService;
    protected $careerTitleDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, CareerTitleService $careerTitleService, CareerTitle $careerTitle)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->careerTitleService = $careerTitleService;
        $this->careerTitle = $careerTitle;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->careerTitle);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->careerTitleDTO = new CareerTitleDTO(
            $this->careerTitleName,
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
        $this->careerTitleService->withParams($this->careerTitleDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->careerTitleName);
            } else {
                $data = $this->careerTitleService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->careerTitleName);
            } else {
                $data = $this->careerTitleService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->careerTitle, $this->careerTitleName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->careerTitleName, $id);
        } else {
            $data = $this->careerTitleService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateCareerTitleRequest $request)
    {
        return $this->careerTitleService->createCareerTitle($request);
    }
    public function update(UpdateCareerTitleRequest $request, $id)
    {
        return $this->careerTitleService->updateCareerTitle($id, $request);
    }
    public function destroy($id)
    {
        return $this->careerTitleService->deleteCareerTitle($id);
    }
}
