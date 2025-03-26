<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PtttCatastropheDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PtttCatastrophe\CreatePtttCatastropheRequest;
use App\Http\Requests\PtttCatastrophe\UpdatePtttCatastropheRequest;
use App\Models\HIS\PtttCatastrophe;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PtttCatastropheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PtttCatastropheController extends BaseApiCacheController
{
    protected $ptttCatastropheService;
    protected $ptttCatastropheDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PtttCatastropheService $ptttCatastropheService, PtttCatastrophe $ptttCatastrophe)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ptttCatastropheService = $ptttCatastropheService;
        $this->ptttCatastrophe = $ptttCatastrophe;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->ptttCatastrophe);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ptttCatastropheDTO = new PtttCatastropheDTO(
            $this->ptttCatastropheName,
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
        );
        $this->ptttCatastropheService->withParams($this->ptttCatastropheDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'pttt_catastrophe_code',
            'pttt_catastrophe_name',
        ];
        $this->elasticCustom = $this->ptttCatastropheService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if (!$keyword) {
                $data = Cache::remember($this->ptttCatastropheName . '_' . $this->param, $this->time, function () use ($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttCatastropheName, $this->elasticCustom, $source);
                    return $data;
                });

            } else {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttCatastropheName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->ptttCatastropheService->handleDataBaseSearch();
            } else {
                $data = $this->ptttCatastropheService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ptttCatastrophe, $this->ptttCatastropheName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ptttCatastropheName, $id);
        } else {
            $data = $this->ptttCatastropheService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePtttCatastropheRequest $request)
    {
        return $this->ptttCatastropheService->createPtttCatastrophe($request);
    }
    public function update(UpdatePtttCatastropheRequest $request, $id)
    {
        return $this->ptttCatastropheService->updatePtttCatastrophe($id, $request);
    }
    public function destroy($id)
    {
        return $this->ptttCatastropheService->deletePtttCatastrophe($id);
    }
}
