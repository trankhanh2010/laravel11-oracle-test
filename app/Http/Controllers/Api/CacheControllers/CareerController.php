<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\CareerDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Career\CreateCareerRequest;
use App\Http\Requests\Career\UpdateCareerRequest;
use App\Models\HIS\Career;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\CareerService;
use Illuminate\Http\Request;


class CareerController extends BaseApiCacheController
{
    protected $careerService;
    protected $careerDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, CareerService $careerService, Career $career)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->careerService = $careerService;
        $this->career = $career;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->career);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->careerDTO = new CareerDTO(
            $this->careerName,
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
        $this->careerService->withParams($this->careerDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->careerName);
            } else {
                $data = $this->careerService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->careerName);
            } else {
                $data = $this->careerService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->career, $this->careerName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->careerName, $id);
        } else {
            $data = $this->careerService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateCareerRequest $request)
    {
        return $this->careerService->createCareer($request);
    }
    public function update(UpdateCareerRequest $request, $id)
    {
        return $this->careerService->updateCareer($id, $request);
    }
    public function destroy($id)
    {
        return $this->careerService->deleteCareer($id);
    }
}
