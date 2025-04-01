<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\FileTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\FileType\CreateFileTypeRequest;
use App\Http\Requests\FileType\UpdateFileTypeRequest;
use App\Models\HIS\FileType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\FileTypeService;
use Illuminate\Http\Request;


class FileTypeController extends BaseApiCacheController
{
    protected $fileTypeService;
    protected $fileTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, FileTypeService $fileTypeService, FileType $fileType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->fileTypeService = $fileTypeService;
        $this->fileType = $fileType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->fileType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->fileTypeDTO = new FileTypeDTO(
            $this->fileTypeName,
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
        $this->fileTypeService->withParams($this->fileTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->fileTypeName);
            } else {
                $data = $this->fileTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->fileTypeName);
            } else {
                $data = $this->fileTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->fileType, $this->fileTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->fileTypeName, $id);
        } else {
            $data = $this->fileTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateFileTypeRequest $request)
    {
        return $this->fileTypeService->createFileType($request);
    }
    public function update(UpdateFileTypeRequest $request, $id)
    {
        return $this->fileTypeService->updateFileType($id, $request);
    }
    public function destroy($id)
    {
        return $this->fileTypeService->deleteFileType($id);
    }
}
