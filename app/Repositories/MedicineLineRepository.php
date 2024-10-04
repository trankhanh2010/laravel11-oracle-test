<?php 
namespace App\Repositories;

use App\Models\HIS\MedicineLine;
use Illuminate\Support\Facades\DB;

class MedicineLineRepository
{
    protected $medicineLine;
    public function __construct(MedicineLine $medicineLine)
    {
        $this->medicineLine = $medicineLine;
    }

    public function applyJoins()
    {
        return $this->medicineLine
            ->select(
                'his_medicine_line.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_line.medicine_line_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medicine_line.medicine_line_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_line.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_medicine_line.' . $key, $item);
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
        return $this->medicineLine->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->medicineLine::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'medicine_line_code' => $request->medicine_line_code,
            'medicine_line_name' => $request->medicine_line_name,
            'num_order'  => $request->num_order,
            'do_not_required_use_form'  => $request->do_not_required_use_form,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'medicine_line_code' => $request->medicine_line_code,
            'medicine_line_name' => $request->medicine_line_name,
            'num_order'  => $request->num_order,
            'do_not_required_use_form'  => $request->do_not_required_use_form,
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
            $data = $data->where('his_medicine_line.id','=', $id)->first();
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