<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DeathWithinDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DeathWithin\CreateDeathWithinRequest;
use App\Http\Requests\DeathWithin\UpdateDeathWithinRequest;
use App\Models\HIS\DeathWithin;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DeathWithinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeathWithinController extends BaseApiCacheController
{
    protected $deathWithinService;
    protected $deathWithinDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DeathWithinService $deathWithinService, DeathWithin $deathWithin)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->deathWithinService = $deathWithinService;
        $this->deathWithin = $deathWithin;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->deathWithin);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->deathWithinDTO = new DeathWithinDTO(
            $this->deathWithinName,
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
            $this->tab,
        );
        $this->deathWithinService->withParams($this->deathWithinDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'death_within_code',
            'death_within_name',
        ];
        $this->elasticCustom = $this->deathWithinService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if(!$keyword){
                $data = Cache::remember($this->deathWithinName.'_' . $this->param, $this->time, function () use($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->deathWithinName, $this->elasticCustom, $source);
                    return $data;
                });
            }else{
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->deathWithinName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->deathWithinService->handleDataBaseSearch();
            } else {
                $data = $this->deathWithinService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->deathWithin, $this->deathWithinName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->deathWithinName, $id);
        } else {
            $data = $this->deathWithinService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateDeathWithinRequest $request)
    {
        return $this->deathWithinService->createDeathWithin($request);
    }
    public function update(UpdateDeathWithinRequest $request, $id)
    {
        return $this->deathWithinService->updateDeathWithin($id, $request);
    }
    public function destroy($id)
    {
        return $this->deathWithinService->deleteDeathWithin($id);
    }
}
