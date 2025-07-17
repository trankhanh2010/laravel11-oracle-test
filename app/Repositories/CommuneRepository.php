<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\SDA\Commune;
use Illuminate\Support\Facades\DB;

class CommuneRepository
{
    protected $commune;
    public function __construct(Commune $commune)
    {
        $this->commune = $commune;
    }

    public function applyJoins()
    {
        return $this->commune
            ->leftJoin('sda_district as district', 'district.id', '=', 'sda_commune.district_id')
            ->select(
                'sda_commune.*',
                'district.district_name',
                'district.district_code',
            );
    }
    public function applyJoinsGetDataSelect()
    {
        return $this->commune
            ->select(
                'sda_commune.id as key',
                'sda_commune.id',
                'sda_commune.commune_code',
                'sda_commune.commune_name',
                'sda_commune.initial_name',
                'sda_commune.province_id',
            );
    }
    public function applyJoinsGetDataSelect2Cap()
    {
        return $this->commune
            ->join('sda_province', function ($join) {
                $join->on('sda_province.id', '=', 'sda_commune.province_id')
                    ->where('sda_province.IS_NO_DISTRICT', 1)
                    ->where('sda_province.is_active', 1)
                    ->where('sda_province.is_delete', 0); // điều kiện bổ sung
            })
            ->select(
                'sda_commune.id as key',
                'sda_commune.id',
                'sda_commune.commune_code',
                'sda_commune.commune_name',
                'sda_commune.initial_name',
                'sda_commune.province_id',
            );
    }
    public function applyJoinsGetDataSelectTHX()
    {
        return $this->commune
            ->join('sda_province', function ($join) {
                $join->on('sda_province.id', '=', 'sda_commune.province_id')
                    ->where('sda_province.IS_NO_DISTRICT', 1)
                    ->where('sda_province.is_active', 1)
                    ->where('sda_province.is_delete', 0); // điều kiện bổ sung
            })
            ->select(
                'sda_commune.id as key',
                'sda_commune.id',
                'sda_commune.commune_code',
                'sda_commune.commune_name',
                'sda_commune.initial_name',
                'sda_commune.province_id',
                'sda_province.province_code',
                'sda_province.province_name',
                DB::raw("sda_commune.search_code || sda_province.search_code AS T_H_X_search_code")
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_sda')->raw('sda_commune.commune_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_sda')->raw('sda_commune.commune_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_sda')->raw('sda_commune.is_active'), $isActive);
        }

        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_sda')->raw('sda_commune.is_delete'), $isDelete);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['district_code', 'district_name'])) {
                        $query->orderBy('district.' . $key, $item);
                    }
                } else {
                    switch ($key) {
                        case 'commune_name':
                            $query->orderByRaw("NLSSORT(sda_commune.commune_name, 'NLS_SORT = Vietnamese') $item");
                            break;
                        case 'province_name':
                            $query->orderByRaw("NLSSORT(sda_province.province_name, 'NLS_SORT = Vietnamese') $item");
                            break;
                        default:
                            $query->orderBy('sda_commune.' . $key, $item);
                            break;
                    }
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->commune->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->commune::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'district_id' => $request->district_id,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'district_id' => $request->district_id,
            'is_active' => $request->is_active,
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('sda_commune.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('sda_commune.id');
            $maxId = $this->applyJoins()->max('sda_commune.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('commune', 'sda_commune', $startId, $endId, $batchSize);
            }
        }
    }
}
