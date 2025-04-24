<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\Phieutdvacsbnc2PhieumauDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\EMR_FINAL\Phieutdvacsbnc2Phieumau;
use Illuminate\Http\Request;
use App\Services\Model\Phieutdvacsbnc2PhieumauService;


class Phieutdvacsbnc2PhieumauController extends BaseApiCacheController
{
    protected $phieutdvacsbnc2PhieumauService;
    protected $phieutdvacsbnc2PhieumauDTO;
    public function __construct(Request $request, Phieutdvacsbnc2PhieumauService $phieutdvacsbnc2PhieumauService, Phieutdvacsbnc2Phieumau $phieutdvacsbnc2Phieumau)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->phieutdvacsbnc2PhieumauService = $phieutdvacsbnc2PhieumauService;
        $this->phieutdvacsbnc2Phieumau = $phieutdvacsbnc2Phieumau;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->phieutdvacsbnc2Phieumau);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->phieutdvacsbnc2PhieumauDTO = new Phieutdvacsbnc2PhieumauDTO(
            $this->phieutdvacsbnc2PhieumauName,
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
        $this->phieutdvacsbnc2PhieumauService->withParams($this->phieutdvacsbnc2PhieumauDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($this->keyword) {
            $data = $this->phieutdvacsbnc2PhieumauService->handleDataBaseSearch();
        } else {
            $data = $this->phieutdvacsbnc2PhieumauService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->phieutdvacsbnc2Phieumau, $this->phieutdvacsbnc2PhieumauName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->phieutdvacsbnc2PhieumauName, $id);
        } else {
            $data = $this->phieutdvacsbnc2PhieumauService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
