<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ConfigDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Config\CreateConfigRequest;
use App\Http\Requests\Config\UpdateConfigRequest;
use App\Models\HIS\Config;
use App\Services\Model\ConfigService;
use Illuminate\Http\Request;


class ConfigController extends BaseApiCacheController
{
    protected $configService;
    protected $configDTO;
    public function __construct(Request $request, ConfigService $configService, Config $config)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->configService = $configService;
        $this->config = $config;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->config);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->configDTO = new ConfigDTO(
            $this->configName,
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
        $this->configService->withParams($this->configDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $data = $this->configService->handleDataBaseGetAll();
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
