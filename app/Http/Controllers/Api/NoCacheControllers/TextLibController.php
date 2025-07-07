<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TextLibDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TextLib\CreateTextLibRequest;
use App\Http\Requests\TextLib\UpdateTextLibRequest;
use App\Models\HIS\TextLib;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TextLibService;
use Illuminate\Http\Request;


class TextLibController extends BaseApiCacheController
{
    protected $textLibService;
    protected $textLibDTO;
    public function __construct(Request $request, TextLibService $textLibService, TextLib $textLib)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->textLibService = $textLibService;
        $this->textLib = $textLib;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->textLib, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->textLibDTO = new TextLibDTO(
            $this->textLibName,
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
            $this->param,
            $this->noCache,
            $this->tab,
            $this->hashTags,
            $this->currentLoginname,
            $this->currentDepartmentId,
        );
        $this->textLibService->withParams($this->textLibDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            $data = $this->textLibService->handleDataBaseSearch();
        } else {
            $data = $this->textLibService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->textLib, $this->textLibName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->textLibService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
