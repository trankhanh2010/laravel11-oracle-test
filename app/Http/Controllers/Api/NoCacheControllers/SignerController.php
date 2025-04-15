<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SignerDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Signer\CreateSignerRequest;
use App\Http\Requests\Signer\UpdateSignerRequest;
use App\Models\EMR\Signer;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SignerService;
use Illuminate\Http\Request;


class SignerController extends BaseApiCacheController
{
    protected $signerService;
    protected $signerDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SignerService $signerService, Signer $signer)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->signerService = $signerService;
        $this->signer = $signer;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->signer);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->signerDTO = new SignerDTO(
            $this->signerName,
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
            $this->param,
            $this->noCache,
        );
        $this->signerService->withParams($this->signerDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->signerService->handleDataBaseGetAll();
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
