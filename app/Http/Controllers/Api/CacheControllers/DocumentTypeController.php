<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DocumentTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DocumentType\CreateDocumentTypeRequest;
use App\Http\Requests\DocumentType\UpdateDocumentTypeRequest;
use App\Models\EMR\DocumentType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DocumentTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class DocumentTypeController extends BaseApiCacheController
{
    protected $documentTypeService;
    protected $documentTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DocumentTypeService $documentTypeService, DocumentType $documentType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->documentTypeService = $documentTypeService;
        $this->documentType = $documentType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->documentType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->documentTypeDTO = new DocumentTypeDTO(
            $this->documentTypeName,
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
        $this->documentTypeService->withParams($this->documentTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'document_type_code',
            'document_type_name',
        ];
        $this->elasticCustom = $this->documentTypeService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if(!$keyword){
                $cacheKey = $this->documentTypeName .'_'. 'elastic' . '_' . $this->param;
                $cacheKeySet = "cache_keys:" . $this->documentTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->time, function () use ($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->documentTypeName, $this->elasticCustom, $source);
                    return $data;
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            }else{
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->documentTypeName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->documentTypeService->handleDataBaseSearch();
            } else {
                $data = $this->documentTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->documentType, $this->documentTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->documentTypeName, $id);
        } else {
            $data = $this->documentTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
