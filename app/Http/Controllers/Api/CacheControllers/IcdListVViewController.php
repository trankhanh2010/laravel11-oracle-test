<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\IcdListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\IcdListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\IcdListVViewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IcdListVViewController extends BaseApiCacheController
{
    protected $icdListVViewService;
    protected $icdListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, IcdListVViewService $icdListVViewService, IcdListVView $icdListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->icdListVViewService = $icdListVViewService;
        $this->icdListVView = $icdListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->icdListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->icdListVViewDTO = new IcdListVViewDTO(
            $this->icdListVViewName,
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
            $this->tab,
            $this->param,
        );
        $this->icdListVViewService->withParams($this->icdListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'icd_code',
            'icd_name',
        ];
        $this->elasticCustom = $this->icdListVViewService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if (!$keyword) {
                $data = Cache::remember($this->icdListVViewName . '_' . $this->param, $this->time, function () use ($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->icdListVViewName, $this->elasticCustom, $source);
                    return base64_encode(gzcompress(serialize($data))); // Nén và mã hóa trước khi lưu
                });
                // **Giải nén khi lấy dữ liệu từ cache**
                if ($data && is_string($data)) {
                    $data = unserialize(gzuncompress(base64_decode($data)));
                }
            } else {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->icdListVViewName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->icdListVViewService->handleDataBaseSearch();
            } else {
                $data = $this->icdListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->icdListVView, $this->icdListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->icdListVViewName, $id);
        } else {
            $data = $this->icdListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
