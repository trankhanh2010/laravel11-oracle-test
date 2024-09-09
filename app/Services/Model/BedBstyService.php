<?php

namespace App\Services\Model;

use App\DTOs\BedBstyDTO;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BedBstyRepository;

class BedBstyService 
{
    protected $bedBstyRepository;
    protected $params;
    public function __construct(BedBstyRepository $bedBstyRepository)
    {
        $this->bedBstyRepository = $bedBstyRepository;
    }
    public function withParams(BedBstyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bedBstyRepository->applyJoins();
            $data = $this->bedBstyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bedBstyRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->bedBstyRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
            $data = $this->bedBstyRepository->applyBedIdsFilter($data, $this->params->bedIds);
            $count = $data->count();
            $data = $this->bedBstyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bedBstyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_bsty'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->bedBstyName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_service_ids_' . $this->params->serviceIds . '_bed_ids_' .$this->params->bedIds . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->bedBstyRepository->applyJoins();
                $data = $this->bedBstyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->bedBstyRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
                $data = $this->bedBstyRepository->applyBedIdsFilter($data, $this->params->bedIds);
                $count = $data->count();
                $data = $this->bedBstyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bedBstyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_bsty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bedBstyName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->bedBstyRepository->applyJoins()
                    ->where('his_bed_bsty.id', $id);
                $data = $this->bedBstyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_bsty'], $e);
        }
    }
}
