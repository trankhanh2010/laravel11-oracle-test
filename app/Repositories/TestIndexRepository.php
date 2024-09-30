<?php 
namespace App\Repositories;

use App\Models\HIS\TestIndex;
use Illuminate\Support\Facades\DB;

class TestIndexRepository
{
    protected $testIndex;
    public function __construct(TestIndex $testIndex)
    {
        $this->testIndex = $testIndex;
    }

    public function applyJoins()
    {
        return $this->testIndex
        ->leftJoin('his_service as service', 'service.id', '=', 'his_test_index.test_service_type_id')
        ->leftJoin('his_test_index_unit as test_index_unit', 'test_index_unit.id', '=', 'his_test_index.test_index_unit_id')
        ->leftJoin('his_test_index_group as test_index_group', 'test_index_group.id', '=', 'his_test_index.test_index_group_id')
        ->leftJoin('his_material_type as material_type', 'material_type.id', '=', 'his_test_index.material_type_id')
        ->leftJoin('his_service_type as test_service_type', 'test_service_type.id', '=', 'his_test_index.test_service_type_id')

            ->select(
                'his_test_index.*',
                'service.service_code',
                'service.service_name',
                'test_index_unit.test_index_unit_code',
                'test_index_unit.test_index_unit_name',
                'test_index_group.test_index_group_code',
                'test_index_group.test_index_group_name',
                'material_type.material_type_code',
                'material_type.material_type_name',
                'test_service_type.service_type_code as test_service_type_code',
                'test_service_type.service_type_name as test_service_type_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_test_index.test_index_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_test_index.test_index_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_test_index.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['service_name', 'service_code'])) {
                        $query->orderBy('service.' . $key, $item);
                    }
                    if (in_array($key, ['test_index_unit_name', 'test_index_unit_code'])) {
                        $query->orderBy('test_index_unit.' . $key, $item);
                    }
                    if (in_array($key, ['test_index_group_name', 'test_index_group_code'])) {
                        $query->orderBy('test_index_group.' . $key, $item);
                    }
                    if (in_array($key, ['material_type_name', 'material_type_code'])) {
                        $query->orderBy('material_type.' . $key, $item);
                    }
                    if (in_array($key, ['test_service_type_name', 'test_service_type_code'])) {
                        $query->orderBy('test_service_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_test_index.' . $key, $item);
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
        return $this->testIndex->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->testIndex::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'test_index_code' => $request->test_index_code,
            'test_index_name' => $request->test_index_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'test_index_code' => $request->test_index_code,
            'test_index_name' => $request->test_index_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_test_index.id','=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes(); 
            })->toArray(); 
        }
        return $data;
    }
}