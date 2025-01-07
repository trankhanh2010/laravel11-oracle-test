<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TransactionListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TransactionListVView\CreateTransactionListVViewRequest;
use App\Http\Requests\TransactionListVView\UpdateTransactionListVViewRequest;
use App\Models\View\TransactionListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TransactionListVViewService;
use Illuminate\Http\Request;


class TransactionListVViewController extends BaseApiCacheController
{
    protected $transactionListVViewService;
    protected $transactionListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TransactionListVViewService $transactionListVViewService, TransactionListVView $transactionListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->transactionListVViewService = $transactionListVViewService;
        $this->transactionListVView = $transactionListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->transactionListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->transactionListVViewDTO = new TransactionListVViewDTO(
            $this->transactionListVViewName,
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
            $this->fromTime,
            $this->toTime,
            $this->transactionTypeIds,
            $this->lastId,
            $this->cursorPaginate,
            $this->treatmentCode,
            $this->transactionCode,
        );
        $this->transactionListVViewService->withParams($this->transactionListVViewDTO);
    }
    public function index()
    {
        // Kiểm tra khoảng cách ngày
        if (($this->fromTime !== null) && ($this->toTime !== null)) {
            if (($this->toTime - $this->fromTime) > 60235959) {
                $this->errors[$this->fromTimeName] = 'Khoảng thời gian vượt quá 60 ngày!';
                $this->fromTime = null;
            }
        }
        if (($this->fromTime == null) && ($this->toTime == null) && (!$this->cursorPaginate)) {
            $this->errors[$this->fromTimeName] = 'Thiếu thời gian!';
            $this->errors[$this->toTimeName] = 'Thiếu thời gian!';
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->transactionListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->transactionListVView, $this->transactionListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->transactionListVViewName, $id);
        } else {
            $data = $this->transactionListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
