<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\FilmSizeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\FilmSize\CreateFilmSizeRequest;
use App\Http\Requests\FilmSize\UpdateFilmSizeRequest;
use App\Models\HIS\FilmSize;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\FilmSizeService;
use Illuminate\Http\Request;


class FilmSizeController extends BaseApiCacheController
{
    protected $filmSizeService;
    protected $filmSizeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, FilmSizeService $filmSizeService, FilmSize $filmSize)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->filmSizeService = $filmSizeService;
        $this->filmSize = $filmSize;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->filmSize);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->filmSizeDTO = new FilmSizeDTO(
            $this->filmSizeName,
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
        $this->filmSizeService->withParams($this->filmSizeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->filmSizeName);
            } else {
                $data = $this->filmSizeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->filmSizeName);
            } else {
                $data = $this->filmSizeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->filmSize, $this->filmSizeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->filmSizeName, $id);
        } else {
            $data = $this->filmSizeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateFilmSizeRequest $request)
    {
        return $this->filmSizeService->createFilmSize($request);
    }
    public function update(UpdateFilmSizeRequest $request, $id)
    {
        return $this->filmSizeService->updateFilmSize($id, $request);
    }
    public function destroy($id)
    {
        return $this->filmSizeService->deleteFilmSize($id);
    }
}
