<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\KskContractDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\KskContract;
use App\Services\Model\KskContractService;
use Illuminate\Http\Request;


class KskContractController extends BaseApiCacheController
{
    protected $kskContractService;
    protected $kskContractDTO;
    public function __construct(Request $request, KskContractService $kskContractService, KskContract $kskContract)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->kskContractService = $kskContractService;
        $this->kskContract = $kskContract;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->kskContract);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->kskContractDTO = new KskContractDTO(
            $this->kskContractName,
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
        $this->kskContractService->withParams($this->kskContractDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword) {
            $data = $this->kskContractService->handleDataBaseSearch();
        } else {
            $data = $this->kskContractService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->kskContract, $this->kskContractName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->kskContractService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
