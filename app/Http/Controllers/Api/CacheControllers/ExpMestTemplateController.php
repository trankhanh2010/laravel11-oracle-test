<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExpMestTemplateDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExpMestTemplate\CreateExpMestTemplateRequest;
use App\Http\Requests\ExpMestTemplate\UpdateExpMestTemplateRequest;
use App\Models\HIS\ExpMestTemplate;
use App\Services\Model\ExpMestTemplateService;
use Illuminate\Http\Request;


class ExpMestTemplateController extends BaseApiCacheController
{
    protected $expMestTemplateService;
    protected $expMestTemplateDTO;
    public function __construct(Request $request, ExpMestTemplateService $expMestTemplateService, ExpMestTemplate $expMestTemplate)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->expMestTemplateService = $expMestTemplateService;
        $this->expMestTemplate = $expMestTemplate;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->expMestTemplate);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->expMestTemplateDTO = new ExpMestTemplateDTO(
            $this->expMestTemplateName,
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
            $this->currentLoginname,
        );
        $this->expMestTemplateService->withParams($this->expMestTemplateDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($this->tab == 'selectByLoginname') {
            $data = $this->expMestTemplateService->handleDataBaseGetAllDataFromDatabaseSelectByLoginname();
        } else {
            if (($keyword != null) && !$this->cache) {
                $data = $this->expMestTemplateService->handleDataBaseSearch();
            } else {
                $data = $this->expMestTemplateService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->exeServiceModule, $this->exeServiceModuleName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->expMestTemplateService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
