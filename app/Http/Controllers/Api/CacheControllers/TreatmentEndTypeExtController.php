<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TreatmentEndTypeExtDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentEndTypeExt\CreateTreatmentEndTypeExtRequest;
use App\Http\Requests\TreatmentEndTypeExt\UpdateTreatmentEndTypeExtRequest;
use App\Models\HIS\TreatmentEndTypeExt;
use App\Services\Model\TreatmentEndTypeExtService;
use Illuminate\Http\Request;


class TreatmentEndTypeExtController extends BaseApiCacheController
{
    protected $treatmentEndTypeExtService;
    protected $treatmentEndTypeExtDTO;
    public function __construct(Request $request, TreatmentEndTypeExtService $treatmentEndTypeExtService, TreatmentEndTypeExt $treatmentEndTypeExt)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatmentEndTypeExtService = $treatmentEndTypeExtService;
        $this->treatmentEndTypeExt = $treatmentEndTypeExt;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->treatmentEndTypeExt);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentEndTypeExtDTO = new TreatmentEndTypeExtDTO(
            $this->treatmentEndTypeExtName,
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
        $this->treatmentEndTypeExtService->withParams($this->treatmentEndTypeExtDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword != null) {
            $data = $this->treatmentEndTypeExtService->handleDataBaseSearch();
        } else {
            $data = $this->treatmentEndTypeExtService->handleDataBaseGetAll();
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
