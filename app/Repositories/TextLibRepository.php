<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\TextLib;
use Illuminate\Support\Facades\DB;

class TextLibRepository
{
    protected $textLib;
    public function __construct(TextLib $textLib)
    {
        $this->textLib = $textLib;
    }

    public function applyJoins()
    {
        return $this->textLib
            ->select(
                'his_text_lib.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('lower(his_text_lib.hot_key)'), 'like', '%' . strtolower($keyword) . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(his_text_lib.title)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_text_lib.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_text_lib.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyHashTagsFilter($query, $param)
    {
        if ($param != null) {
            foreach ($param as $key => $item) {
                if ($item) {
                    $query->where(DB::connection('oracle_his')->raw('lower(his_text_lib.hashtag)'), 'like', '%' . strtolower($item) . '%');
                }
            }
        }
        return $query;
    }
    public function applyTabFilter($query, $param, $currentLoginname, $currentDepartmentId)
    {
        switch ($param) {
            case 'yeuCauKhamClsPttt':
                $query
                    ->where(function ($qr) use ($currentLoginname, $currentDepartmentId) {
                        $qr->where(function ($q) use ($currentLoginname) {
                            $q->where('his_text_lib.is_public', 1)
                                ->orWhere('creator', $currentLoginname);
                        })
                            ->orWhere(function ($q) use ($currentDepartmentId) {
                                $q->where('his_text_lib.department_id', $currentDepartmentId)
                                    ->where('his_text_lib.is_public_in_department', 1);
                            });
                    });
                return $query;
            default:
                return $query;
        }
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_text_lib.' . $key, $item);
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
        return $this->textLib->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->textLib::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'title' => $request->title,
    //         'content' => $request->content,
    //         'hashtag' => $request->hashtag,
    //         'isPublic' => $request->isPublic,            
    //         'hashtag' => $request->hashtag,
    //         'hashtag' => $request->hashtag,            
    //         'hashtag' => $request->hashtag,
    //         'hashtag' => $request->hashtag,

    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'text_lib_code' => $request->text_lib_code,
    //         'text_lib_name' => $request->text_lib_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_text_lib.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_text_lib.id');
            $maxId = $this->applyJoins()->max('his_text_lib.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('text_lib', 'his_text_lib', $startId, $endId, $batchSize);
            }
        }
    }
}
