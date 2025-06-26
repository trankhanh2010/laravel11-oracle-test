<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\AcinInteractiveDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AcinInteractive\CreateAcinInteractiveRequest;
use App\Http\Requests\AcinInteractive\UpdateAcinInteractiveRequest;
use App\Models\HIS\AcinInteractive;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AcinInteractiveService;
use Illuminate\Http\Request;


class AcinInteractiveController extends BaseApiCacheController
{
    protected $acinInteractiveService;
    protected $acinInteractiveDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AcinInteractiveService $acinInteractiveService, AcinInteractive $acinInteractive)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->acinInteractiveService = $acinInteractiveService;
        $this->acinInteractive = $acinInteractive;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->acinInteractive);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->acinInteractiveDTO = new AcinInteractiveDTO(
            $this->acinInteractiveName,
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
            $this->groupBy,
        );
        $this->acinInteractiveService->withParams($this->acinInteractiveDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->acinInteractiveName);
            } else {
                $data = $this->acinInteractiveService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->acinInteractiveName);
            } else {
                $data = $this->acinInteractiveService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->acinInteractive, $this->acinInteractiveName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->acinInteractiveName, $id);
        } else {
            $data = $this->acinInteractiveService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
