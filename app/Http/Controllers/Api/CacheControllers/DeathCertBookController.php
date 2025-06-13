<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DeathCertBookDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DeathCertBook\CreateDeathCertBookRequest;
use App\Http\Requests\DeathCertBook\UpdateDeathCertBookRequest;
use App\Models\HIS\DeathCertBook;
use App\Services\Model\DeathCertBookService;
use Illuminate\Http\Request;


class DeathCertBookController extends BaseApiCacheController
{
    protected $deathCertBookService;
    protected $deathCertBookDTO;
    public function __construct(Request $request, DeathCertBookService $deathCertBookService, DeathCertBook $deathCertBook)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->deathCertBookService = $deathCertBookService;
        $this->deathCertBook = $deathCertBook;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->deathCertBook);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->deathCertBookDTO = new DeathCertBookDTO(
            $this->deathCertBookName,
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
        $this->deathCertBookService->withParams($this->deathCertBookDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword != null) {
            $data = $this->deathCertBookService->handleDataBaseSearch();
        } else {
            $data = $this->deathCertBookService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->deathCertBook, $this->deathCertBookName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->deathCertBookName, $id);
        } else {
            $data = $this->deathCertBookService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
