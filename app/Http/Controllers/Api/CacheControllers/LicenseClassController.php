<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\LicenseClassDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\LicenseClass\CreateLicenseClassRequest;
use App\Http\Requests\LicenseClass\UpdateLicenseClassRequest;
use App\Models\HIS\LicenseClass;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\LicenseClassService;
use Illuminate\Http\Request;


class LicenseClassController extends BaseApiCacheController
{
    protected $licenseClassService;
    protected $licenseClassDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, LicenseClassService $licenseClassService, LicenseClass $licenseClass)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->licenseClassService = $licenseClassService;
        $this->licenseClass = $licenseClass;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->licenseClass);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->licenseClassDTO = new LicenseClassDTO(
            $this->licenseClassName,
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
        );
        $this->licenseClassService->withParams($this->licenseClassDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->licenseClassName);
            } else {
                $data = $this->licenseClassService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->licenseClassName);
            } else {
                $data = $this->licenseClassService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->licenseClass, $this->licenseClassName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->licenseClassName, $id);
        } else {
            $data = $this->licenseClassService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateLicenseClassRequest $request)
    {
        return $this->licenseClassService->createLicenseClass($request);
    }
    public function update(UpdateLicenseClassRequest $request, $id)
    {
        return $this->licenseClassService->updateLicenseClass($id, $request);
    }
    public function destroy($id)
    {
        return $this->licenseClassService->deleteLicenseClass($id);
    }
}
