<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\EmotionlessMethodDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\EmotionlessMethod\CreateEmotionlessMethodRequest;
use App\Http\Requests\EmotionlessMethod\UpdateEmotionlessMethodRequest;
use App\Models\HIS\EmotionlessMethod;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\EmotionlessMethodService;
use Illuminate\Http\Request;


class EmotionlessMethodController extends BaseApiCacheController
{
    protected $emotionlessMethodService;
    protected $emotionlessMethodDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, EmotionlessMethodService $emotionlessMethodService, EmotionlessMethod $emotionlessMethod)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->emotionlessMethodService = $emotionlessMethodService;
        $this->emotionlessMethod = $emotionlessMethod;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->emotionlessMethod);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->emotionlessMethodDTO = new EmotionlessMethodDTO(
            $this->emotionlessMethodName,
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
        $this->emotionlessMethodService->withParams($this->emotionlessMethodDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->emotionlessMethodName);
            } else {
                $data = $this->emotionlessMethodService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->emotionlessMethodName);
            } else {
                $data = $this->emotionlessMethodService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->emotionlessMethod, $this->emotionlessMethodName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->emotionlessMethodName, $id);
        } else {
            $data = $this->emotionlessMethodService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateEmotionlessMethodRequest $request)
    {
        return $this->emotionlessMethodService->createEmotionlessMethod($request);
    }
    public function update(UpdateEmotionlessMethodRequest $request, $id)
    {
        return $this->emotionlessMethodService->updateEmotionlessMethod($id, $request);
    }
    public function destroy($id)
    {
        return $this->emotionlessMethodService->deleteEmotionlessMethod($id);
    }
}
