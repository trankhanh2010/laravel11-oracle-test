<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DocumentBookDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DocumentBook\CreateDocumentBookRequest;
use App\Http\Requests\DocumentBook\UpdateDocumentBookRequest;
use App\Models\HIS\DocumentBook;
use App\Services\Model\DocumentBookService;
use Illuminate\Http\Request;


class DocumentBookController extends BaseApiCacheController
{
    protected $documentBookService;
    protected $documentBookDTO;
    public function __construct(Request $request, DocumentBookService $documentBookService, DocumentBook $documentBook)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->documentBookService = $documentBookService;
        $this->documentBook = $documentBook;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->documentBook);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->documentBookDTO = new DocumentBookDTO(
            $this->documentBookName,
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
            $this->tab,
        );
        $this->documentBookService->withParams($this->documentBookDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null) && !$this->cache) {
            $data = $this->documentBookService->handleDataBaseSearch();
        } else {
            $data = $this->documentBookService->handleDataBaseGetAll();
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
}
