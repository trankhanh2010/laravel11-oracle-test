<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\EmrCoverTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\EmrCoverType\CreateEmrCoverTypeRequest;
use App\Http\Requests\EmrCoverType\UpdateEmrCoverTypeRequest;
use App\Models\HIS\EmrCoverType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\EmrCoverTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmrCoverTypeController extends BaseApiCacheController
{
    protected $emrCoverTypeService;
    protected $emrCoverTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, EmrCoverTypeService $emrCoverTypeService, EmrCoverType $emrCoverType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->emrCoverTypeService = $emrCoverTypeService;
        $this->emrCoverType = $emrCoverType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->emrCoverType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->emrCoverTypeDTO = new EmrCoverTypeDTO(
            $this->emrCoverTypeName,
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
            $this->param,
        );
        $this->emrCoverTypeService->withParams($this->emrCoverTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'emr_cover_type_code',
            'emr_cover_type_name',
        ];
        $this->elasticCustom = $this->emrCoverTypeService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if(!$keyword){
                $data = Cache::remember($this->emrCoverTypeName.'_' . $this->param, $this->time, function () use($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->emrCoverTypeName, $this->elasticCustom, $source);
                    return $data;
                });
            }else{
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->emrCoverTypeName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->emrCoverTypeService->handleDataBaseSearch();
            } else {
                $data = $this->emrCoverTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->emrCoverType, $this->emrCoverTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->emrCoverTypeName, $id);
        } else {
            $data = $this->emrCoverTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
