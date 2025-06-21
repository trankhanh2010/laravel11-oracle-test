<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExpMestTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExpMestType\CreateExpMestTypeRequest;
use App\Http\Requests\ExpMestType\UpdateExpMestTypeRequest;
use App\Models\HIS\ExpMestType;
use App\Services\Model\ExpMestTypeService;
use Illuminate\Http\Request;


class ExpMestTypeController extends BaseApiCacheController
{
    protected $expMestTypeService;
    protected $expMestTypeDTO;
    public function __construct(Request $request, ExpMestTypeService $expMestTypeService, ExpMestType $expMestType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->expMestTypeService = $expMestTypeService;
        $this->expMestType = $expMestType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->expMestType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->expMestTypeDTO = new ExpMestTypeDTO(
            $this->expMestTypeName,
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
        $this->expMestTypeService->withParams($this->expMestTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            $data = $this->expMestTypeService->handleDataBaseSearch();
        } else {
            $data = $this->expMestTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->expMestType, $this->expMestTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->expMestTypeService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
