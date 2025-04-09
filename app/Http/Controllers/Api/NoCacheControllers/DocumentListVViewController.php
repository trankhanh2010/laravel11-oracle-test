<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\DocumentListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\DocumentListVView;
use App\Services\Model\DocumentListVViewService;
use Illuminate\Http\Request;


class DocumentListVViewController extends BaseApiCacheController
{
    protected $documentListVViewService;
    protected $documentListVViewDTO;
    public function __construct(Request $request, DocumentListVViewService $documentListVViewService, DocumentListVView $documentListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->documentListVViewService = $documentListVViewService;
        $this->documentListVView = $documentListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->documentListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->documentListVViewDTO = new DocumentListVViewDTO(
            $this->documentListVViewName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->treatmentId,
            $this->documentTypeId,
            $this->treatmentCode,
            $this->param,
            $this->noCache,
            $this->documentIds,
            $this->groupBy,
        );
        $this->documentListVViewService->withParams($this->documentListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if($this->documentIds != null){
            $data = $this->documentListVViewService->handleMergeDocumentByIds();
        }else{
            if($this->treatmentCode == null && $this->treatmentId == null){
                return returnDataSuccess(null, []);
            }
            $data = $this->documentListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->documentListVView, $this->documentListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->documentListVViewName, $id);
        } else {
            $data = $this->documentListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
