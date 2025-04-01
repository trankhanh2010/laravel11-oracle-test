<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BidDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Bid\CreateBidRequest;
use App\Http\Requests\Bid\UpdateBidRequest;
use App\Models\HIS\Bid;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BidService;
use Illuminate\Http\Request;


class BidController extends BaseApiCacheController
{
    protected $bidService;
    protected $bidDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BidService $bidService, Bid $bid)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bidService = $bidService;
        $this->bid = $bid;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bid);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bidDTO = new BidDTO(
            $this->bidName,
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
        $this->bidService->withParams($this->bidDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bidName);
            } else {
                $data = $this->bidService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bidName);
            } else {
                $data = $this->bidService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bid, $this->bidName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bidName, $id);
        } else {
            $data = $this->bidService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBidRequest $request)
    {
        return $this->bidService->createBid($request);
    }
    public function update(UpdateBidRequest $request, $id)
    {
        return $this->bidService->updateBid($id, $request);
    }
    public function destroy($id)
    {
        return $this->bidService->deleteBid($id);
    }
}
