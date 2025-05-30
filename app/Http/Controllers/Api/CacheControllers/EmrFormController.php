<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\EmrFormDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\EmrForm\CreateEmrFormRequest;
use App\Http\Requests\EmrForm\UpdateEmrFormRequest;
use App\Models\HIS\EmrForm;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\EmrFormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class EmrFormController extends BaseApiCacheController
{
    protected $emrFormService;
    protected $emrFormDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, EmrFormService $emrFormService, EmrForm $emrForm)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->emrFormService = $emrFormService;
        $this->emrForm = $emrForm;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->emrForm);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->emrFormDTO = new EmrFormDTO(
            $this->emrFormName,
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
            $this->noCache,
        );
        $this->emrFormService->withParams($this->emrFormDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'emr_form_code',
            'emr_form_name',
        ];
        $this->elasticCustom = $this->emrFormService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if(!$keyword){
                $cacheKey = $this->emrFormName .'_'. 'elastic' . '_' . $this->param;
                $cacheKeySet = "cache_keys:" . $this->emrFormName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->time, function () use ($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->emrFormName, $this->elasticCustom, $source);
                    return $data;
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            }else{
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->emrFormName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->emrFormService->handleDataBaseSearch();
            } else {
                $data = $this->emrFormService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->emrForm, $this->emrFormName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->emrFormName, $id);
        } else {
            $data = $this->emrFormService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
