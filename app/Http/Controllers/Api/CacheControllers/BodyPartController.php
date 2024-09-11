<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BodyPartDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BodyPart\CreateBodyPartRequest;
use App\Http\Requests\BodyPart\UpdateBodyPartRequest;
use App\Models\HIS\BodyPart;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BodyPartService;
use Illuminate\Http\Request;


class BodyPartController extends BaseApiCacheController
{
    protected $bodyPartService;
    protected $bodyPartDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BodyPartService $bodyPartService, BodyPart $bodyPart)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bodyPartService = $bodyPartService;
        $this->bodyPart = $bodyPart;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bodyPart);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bodyPartDTO = new BodyPartDTO(
            $this->bodyPartName,
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
        );
        $this->bodyPartService->withParams($this->bodyPartDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bodyPartName);
            } else {
                $data = $this->bodyPartService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bodyPartName);
            } else {
                $data = $this->bodyPartService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bodyPart, $this->bodyPartName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bodyPartName, $id);
        } else {
            $data = $this->bodyPartService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBodyPartRequest $request)
    {
        return $this->bodyPartService->createBodyPart($request);
    }
    public function update(UpdateBodyPartRequest $request, $id)
    {
        return $this->bodyPartService->updateBodyPart($id, $request);
    }
    public function destroy($id)
    {
        return $this->bodyPartService->deleteBodyPart($id);
    }
}
