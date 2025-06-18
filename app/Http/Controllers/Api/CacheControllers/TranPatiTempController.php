<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TranPatiTempDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;

use App\Models\HIS\TranPatiTemp;
use App\Services\Model\TranPatiTempService;
use Illuminate\Http\Request;


class TranPatiTempController extends BaseApiCacheController
{
    protected $tranPatiTempService;
    protected $tranPatiTempDTO;
    public function __construct(Request $request, TranPatiTempService $tranPatiTempService, TranPatiTemp $tranPatiTemp)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->tranPatiTempService = $tranPatiTempService;
        $this->tranPatiTemp = $tranPatiTemp;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->tranPatiTemp);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->tranPatiTempDTO = new TranPatiTempDTO(
            $this->tranPatiTempName,
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
        $this->tranPatiTempService->withParams($this->tranPatiTempDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($this->tab == 'selectByLoginname') {
            $data = $this->tranPatiTempService->handleDataBaseGetAllDataFromDatabaseSelectByLoginname();
        } else {
            $keyword = $this->keyword;
            if (($keyword != null) && !$this->cache) {
                $data = $this->tranPatiTempService->handleDataBaseSearch();
            } else {
                $data = $this->tranPatiTempService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->tranPatiTemp, $this->tranPatiTempName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->tranPatiTempService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
