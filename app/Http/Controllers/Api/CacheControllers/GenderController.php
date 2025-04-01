<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\GenderDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Gender\CreateGenderRequest;
use App\Http\Requests\Gender\UpdateGenderRequest;
use App\Models\HIS\Gender;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\GenderService;
use Illuminate\Http\Request;


class GenderController extends BaseApiCacheController
{
    protected $genderService;
    protected $genderDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, GenderService $genderService, Gender $gender)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->genderService = $genderService;
        $this->gender = $gender;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->gender);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->genderDTO = new GenderDTO(
            $this->genderName,
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
        $this->genderService->withParams($this->genderDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->genderName);
            } else {
                $data = $this->genderService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->genderName);
            } else {
                $data = $this->genderService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->gender, $this->genderName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->genderName, $id);
        } else {
            $data = $this->genderService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateGenderRequest $request)
    {
        return $this->genderService->createGender($request);
    }
    public function update(UpdateGenderRequest $request, $id)
    {
        return $this->genderService->updateGender($id, $request);
    }
    public function destroy($id)
    {
        return $this->genderService->deleteGender($id);
    }
}
