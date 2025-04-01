<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BloodVolumeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BloodVolume\CreateBloodVolumeRequest;
use App\Http\Requests\BloodVolume\UpdateBloodVolumeRequest;
use App\Models\HIS\BloodVolume;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BloodVolumeService;
use Illuminate\Http\Request;


class BloodVolumeController extends BaseApiCacheController
{
    protected $bloodVolumeService;
    protected $bloodVolumeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BloodVolumeService $bloodVolumeService, BloodVolume $bloodVolume)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bloodVolumeService = $bloodVolumeService;
        $this->bloodVolume = $bloodVolume;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bloodVolume);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bloodVolumeDTO = new BloodVolumeDTO(
            $this->bloodVolumeName,
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
        $this->bloodVolumeService->withParams($this->bloodVolumeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bloodVolumeName);
            } else {
                $data = $this->bloodVolumeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bloodVolumeName);
            } else {
                $data = $this->bloodVolumeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bloodVolume, $this->bloodVolumeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bloodVolumeName, $id);
        } else {
            $data = $this->bloodVolumeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBloodVolumeRequest $request)
    {
        return $this->bloodVolumeService->createBloodVolume($request);
    }
    public function update(UpdateBloodVolumeRequest $request, $id)
    {
        return $this->bloodVolumeService->updateBloodVolume($id, $request);
    }
    public function destroy($id)
    {
        return $this->bloodVolumeService->deleteBloodVolume($id);
    }
}
