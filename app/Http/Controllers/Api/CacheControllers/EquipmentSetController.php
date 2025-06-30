<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\EquipmentSetDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\EquipmentSet\CreateEquipmentSetRequest;
use App\Http\Requests\EquipmentSet\UpdateEquipmentSetRequest;
use App\Models\HIS\EquipmentSet;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\EquipmentSetService;
use Illuminate\Http\Request;


class EquipmentSetController extends BaseApiCacheController
{
    protected $equipmentSetService;
    protected $equipmentSetDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, EquipmentSetService $equipmentSetService, EquipmentSet $equipmentSet)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->equipmentSetService = $equipmentSetService;
        $this->equipmentSet = $equipmentSet;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->equipmentSet);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->equipmentSetDTO = new EquipmentSetDTO(
            $this->equipmentSetName,
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
        $this->equipmentSetService->withParams($this->equipmentSetDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword) {
            $data = $this->equipmentSetService->handleDataBaseSearch();
        } else {
            $data = $this->equipmentSetService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->equipmentSet, $this->equipmentSetName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->equipmentSetService->getDataById($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
