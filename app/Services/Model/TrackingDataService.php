<?php

namespace App\Services\Model;

use App\DTOs\TrackingDataDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TrackingData\InsertTrackingDataIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\ExpMestBltyReqVView2Repository;
use App\Repositories\ExpMestMaterialRepository;
use App\Repositories\ExpMestMedicineRepository;
use App\Repositories\ExpMestRepository;
use App\Repositories\ImpMestBloodVViewRepository;
use App\Repositories\ImpMestMaterialVViewRepository;
use App\Repositories\ImpMestMedicineVViewRepository;
use App\Repositories\ImpMestVView2Repository;
use App\Repositories\SereServRationVViewRepository;
use App\Repositories\SereServRepository;
use App\Repositories\ServiceReqMatyRepository;
use App\Repositories\ServiceReqMetyRepository;
use App\Repositories\ServiceReqRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrackingDataRepository;
use App\Repositories\TreatmentRepository;

class TrackingDataService
{
    protected $treatmentRepository;
    protected $serviceReqRepository;
    protected $expMestRepository;
    protected $impMestVView2Repository; 
    protected $expMestMedicineRepository;
    protected $expMestMaterialRepository;
    protected $impMestMedicineVViewRepository;
    protected $impMestMaterialVViewRepository;
    protected $impMestBloodVViewRepository;
    protected $serviceReqMetyRepository;
    protected $serviceReqMatyRepository;
    protected $sereServRationVViewRepository;
    protected $expMestBltyReqVView2Repository;
    protected $sereServRepository;
    protected $params;
    public function __construct(
        TreatmentRepository $treatmentRepository, 
        ServiceReqRepository $serviceReqRepository, 
        ExpMestRepository $expMestRepository,
        ImpMestVView2Repository $impMestVView2Repository,
        ExpMestMedicineRepository $expMestMedicineRepository,
        ExpMestMaterialRepository $expMestMaterialRepository,
        ImpMestMedicineVViewRepository $impMestMedicineVViewRepository,
        ImpMestMaterialVViewRepository $impMestMaterialVViewRepository,
        ImpMestBloodVViewRepository $impMestBloodVViewRepository,
        ServiceReqMetyRepository $serviceReqMetyRepository,
        ServiceReqMatyRepository $serviceReqMatyRepository,
        SereServRationVViewRepository $sereServRationVViewRepository,
        ExpMestBltyReqVView2Repository $expMestBltyReqVView2Repository,
        SereServRepository $sereServRepository,
        )
    {
        $this->treatmentRepository = $treatmentRepository;
        $this->serviceReqRepository = $serviceReqRepository;
        $this->expMestRepository = $expMestRepository;
        $this->impMestVView2Repository = $impMestVView2Repository;
        $this->expMestMedicineRepository = $expMestMedicineRepository;
        $this->expMestMaterialRepository = $expMestMaterialRepository;
        $this->impMestMedicineVViewRepository = $impMestMedicineVViewRepository;
        $this->impMestMaterialVViewRepository = $impMestMaterialVViewRepository;
        $this->impMestBloodVViewRepository = $impMestBloodVViewRepository;
        $this->serviceReqMetyRepository = $serviceReqMetyRepository;
        $this->serviceReqMatyRepository = $serviceReqMatyRepository;
        $this->sereServRationVViewRepository = $sereServRationVViewRepository;
        $this->expMestBltyReqVView2Repository = $expMestBltyReqVView2Repository;
        $this->sereServRepository = $sereServRepository;
    }
    public function withParams(TrackingDataDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    // public function handleDataBaseSearch()
    // {
    //     try {
    //         $data = $this->trackingDataRepository->applyJoins();
    //         $data = $this->trackingDataRepository->applyKeywordFilter($data, $this->params->keyword);
    //         $data = $this->trackingDataRepository->applyIsActiveFilter($data, $this->params->isActive);
    //         $data = $this->trackingDataRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    //         $count = $data->count();
    //         $data = $this->trackingDataRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
    //         $data = $this->trackingDataRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
    //         return ['data' => $data, 'count' => $count];
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }
    public function handleDataBaseGetAll()
    {
        // try {
        //     $data = [];
        //     $count = null;
        //     $data['Treatment'] = $this->treatmentRepository->applyJoins();
        //     $data['Treatment'] = $this->treatmentRepository->applyWith($data['Treatment'])->where('his_treatment.id', $this->params->treatmentId)->get();
        //     if(in_array('ServiceReqs', $this->params->relations)){
        //         $data['ServiceReqs'] = $this->serviceReqRepository->applyJoins();
        //         $data['ServiceReqs'] = $this->serviceReqRepository->applyWith($data['ServiceReqs']);
        //         $data['ServiceReqs'] = $this->serviceReqRepository->applyTreatmentIdFilter($data['ServiceReqs'], $this->params->treatmentId);
        //         $data['ServiceReqs'] = $data['ServiceReqs']->get();
        //     }
        //     if(in_array('ExpMests', $this->params->relations)){
        //         $data['ExpMests'] = $this->expMestRepository->applyJoins();
        //         $data['ExpMests'] = $this->expMestRepository->applyWith($data['ExpMests']);
        //         $data['ExpMests'] = $this->expMestRepository->applyTdlTreatmentIdFilter($data['ExpMests'], $this->params->treatmentId);
        //         $data['ExpMests'] = $data['ExpMests']->get();
        //     }
        //     if(in_array('ImpMestsVView2', $this->params->relations)){
        //         $data['ImpMestsVView2'] = $this->impMestVView2Repository->applyJoins();
        //         $data['ImpMestsVView2'] = $this->impMestVView2Repository->applyTdlTreatmentIdFilter($data['ImpMestsVView2'], $this->params->treatmentId);
        //         $data['ImpMestsVView2'] = $data['ImpMestsVView2']->get();
        //     }
        //     if(in_array('ExpMestMedicines', $this->params->relations)){
        //         $data['ExpMestMedicines'] = $this->expMestMedicineRepository->applyJoins();
        //         $data['ExpMestMedicines'] = $this->expMestMedicineRepository->applyWith($data['ExpMestMedicines']);
        //         $data['ExpMestMedicines'] = $this->expMestMedicineRepository->applyTdlTreatmentIdFilter($data['ExpMestMedicines'], $this->params->treatmentId);
        //         $data['ExpMestMedicines'] = $data['ExpMestMedicines']->get();
        //     }
        //     if(in_array('ExpMestMaterials', $this->params->relations)){
        //         $data['ExpMestMaterials'] = $this->expMestMaterialRepository->applyJoins();
        //         $data['ExpMestMaterials'] = $this->expMestMaterialRepository->applyWith($data['ExpMestMaterials']);
        //         $data['ExpMestMaterials'] = $this->expMestMaterialRepository->applyTdlTreamentIdFilter($data['ExpMestMaterials'], $this->params->treatmentId);
        //         $data['ExpMestMaterials'] = $data['ExpMestMaterials']->get();
        //     }
        //     if(in_array('ImpMestMedicinesVView', $this->params->relations)){
        //         $data['ImpMestMedicinesVView'] = $this->impMestMedicineVViewRepository->applyJoins();
        //         $data['ImpMestMedicinesVView'] = $this->impMestMedicineVViewRepository->applyTdlTreatmentIdFilter($data['ImpMestMedicinesVView'], $this->params->treatmentId);
        //         $data['ImpMestMedicinesVView'] = $data['ImpMestMedicinesVView']->get();
        //     }
        //     if(in_array('ImpMestMaterialsVView', $this->params->relations)){
        //         $data['ImpMestMaterialsVView'] = $this->impMestMaterialVViewRepository->applyJoins();
        //         $data['ImpMestMaterialsVView'] = $this->impMestMaterialVViewRepository->applyTdlTreatmentIdFilter($data['ImpMestMaterialsVView'], $this->params->treatmentId);
        //         $data['ImpMestMaterialsVView'] = $data['ImpMestMaterialsVView']->get();
        //     }
        //     if(in_array('ImpMestBloodsVView', $this->params->relations)){
        //         $data['ImpMestBloodsVView'] = $this->impMestBloodVViewRepository->applyJoins();
        //         $data['ImpMestBloodsVView'] = $this->impMestBloodVViewRepository->applyTdlTreatmentIdFilter($data['ImpMestBloodsVView'], $this->params->treatmentId);
        //         $data['ImpMestBloodsVView'] = $data['ImpMestBloodsVView']->get();
        //     }
        //     if(in_array('ServiceReqMetys', $this->params->relations)){
        //         $data['ServiceReqMetys'] = $this->serviceReqMetyRepository->applyJoins();
        //         $data['ServiceReqMetys'] = $this->serviceReqMetyRepository->applyTdlTreatmentIdFilter($data['ServiceReqMetys'], $this->params->treatmentId);
        //         $data['ServiceReqMetys'] = $data['ServiceReqMetys']->get();
        //     }
        //     if(in_array('ServiceReqMatys', $this->params->relations)){
        //         $data['ServiceReqMatys'] = $this->serviceReqMatyRepository->applyJoins();
        //         $data['ServiceReqMatys'] = $this->serviceReqMatyRepository->applyTdlTreatmentIdFilter($data['ServiceReqMatys'], $this->params->treatmentId);
        //         $data['ServiceReqMatys'] = $data['ServiceReqMatys']->get();
        //     }
        //     if(in_array('SereServRationsVView', $this->params->relations)){
        //         $data['SereServRationsVView'] = $this->sereServRationVViewRepository->applyJoins();
        //         $data['SereServRationsVView'] = $this->sereServRationVViewRepository->applyTreatmentIdFilter($data['SereServRationsVView'], $this->params->treatmentId);
        //         $data['SereServRationsVView'] = $data['SereServRationsVView']->get();
        //     }
        //     if(in_array('ExpMestBltyReqsVView2', $this->params->relations)){
        //         $data['ExpMestBltyReqsVView2'] = $this->expMestBltyReqVView2Repository->applyJoins();
        //         $data['ExpMestBltyReqsVView2'] = $this->expMestBltyReqVView2Repository->applyTdlTreatmentIdFilter($data['ExpMestBltyReqsVView2'], $this->params->treatmentId);
        //         $data['ExpMestBltyReqsVView2'] = $data['ExpMestBltyReqsVView2']->get();
        //     }
        //     if(in_array('SereServs', $this->params->relations)){
        //         $data['SereServs'] = $this->sereServRepository->applyJoins();
        //         $data['SereServs'] = $this->sereServRepository->applyWith($data['SereServs']);
        //         $data['SereServs'] = $this->sereServRepository->applyTreatmentIdFilter($data['SereServs'], $this->params->treatmentId);
        //         $data['SereServs'] = $data['SereServs']->get();
        //     }
        //     return ['data' => $data, 'count' => $count];
        // } catch (\Throwable $e) {
        //     return writeAndThrowError(config('params')['db_service']['error']['tracking_data'], $e);
        // }
    }
    // public function handleDataBaseGetWithId($id)
    // {
    //     try {
    //         $data = $this->trackingDataRepository->applyJoins()
    //             ->where('his_debate_user.id', $id);
    //         $data = $this->trackingDataRepository->applyIsActiveFilter($data, $this->params->isActive);
    //         $data = $this->trackingDataRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    //         $data = $data->first();
    //         return $data;
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }

    // public function createTrackingData($request)
    // {
    //     try {
    //         $data = $this->trackingDataRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingDataName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTrackingDataIndex($data, $this->params->trackingDataName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }

    // public function updateTrackingData($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->trackingDataRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->trackingDataRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingDataName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTrackingDataIndex($data, $this->params->trackingDataName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }

    // public function deleteTrackingData($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->trackingDataRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->trackingDataRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingDataName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->trackingDataName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }
}
