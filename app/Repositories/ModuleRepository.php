<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\ACS\Module;
use Illuminate\Support\Facades\DB;

class ModuleRepository
{
    protected $module;
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function applyJoins()
    {
        return $this->module
        ->leftJoin('acs_module_group as module_group', 'module_group.id', '=', 'acs_module.module_group_id')
            ->select(
                'acs_module.*',
                'module_group.module_group_code',
                'module_group.module_group_name'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_acs')->raw('acs_module.module_link'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_acs')->raw('acs_module.module_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_acs')->raw('acs_module.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('acs_module.' . $key, $item);
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
        return $this->module->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->module::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'module_link' => $request->module_link,
            'module_name' => $request->module_name,
            'is_anonymous' => $request->is_anonymous,
            'application_id' => $request->application_id,
            'icon_link' => $request->icon_link,
            'module_url' => $request->module_url,
            'video_urls' => $request->video_urls,
            'num_order'  => $request->num_order,
            'parent_id'  => $request->parent_id,
            'module_group_id' => $request->module_group_id,
            'is_visible' => $request->is_visible,
            'is_not_show_dialog' => $request->is_not_show_dialog  
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'module_link' => $request->module_link,
            'module_name' => $request->module_name,
            'is_anonymous' => $request->is_anonymous,
            'application_id' => $request->application_id,
            'icon_link' => $request->icon_link,
            'module_url' => $request->module_url,
            'video_urls' => $request->video_urls,
            'num_order'  => $request->num_order,
            'parent_id'  => $request->parent_id,
            'is_visible' => $request->is_visible,
            'is_not_show_dialog' => $request->is_not_show_dialog,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('acs_module.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('acs_module.id');
            $maxId = $this->applyJoins()->max('acs_module.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('module', 'acs_module', $startId, $endId, $batchSize);
            }
        }
    }
}