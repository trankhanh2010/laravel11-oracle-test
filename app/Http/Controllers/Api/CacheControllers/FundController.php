<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\FundDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Fund\CreateFundRequest;
use App\Http\Requests\Fund\UpdateFundRequest;
use App\Models\HIS\Fund;
use App\Services\Model\FundService;
use Illuminate\Http\Request;


class FundController extends BaseApiCacheController
{
    protected $fundService;
    protected $fundDTO;
    public function __construct(Request $request, FundService $fundService, Fund $fund)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->fundService = $fundService;
        $this->fund = $fund;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->fund);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->fundDTO = new FundDTO(
            $this->fundName,
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
        $this->fundService->withParams($this->fundDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword == null) {
            if ($this->keyword == null) {
                $data = $this->fundService->handleDataBaseGetAll();
            } else {
                $data = $this->fundService->handleDataBaseSearch();
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
            $validationError = $this->validateAndCheckId($id, $this->fund, $this->fundName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->fundName, $id);
        } else {
            $data = $this->fundService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    // public function store(CreateFundRequest $request)
    // {
    //     return $this->fundService->createFund($request);
    // }
    // public function update(UpdateFundRequest $request, $id)
    // {
    //     return $this->fundService->updateFund($id, $request);
    // }
    // public function destroy($id)
    // {
    //     return $this->fundService->deleteFund($id);
    // }
}
