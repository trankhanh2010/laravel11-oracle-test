<?php 
namespace App\Repositories;

use App\Models\HIS\Icd;
use Illuminate\Support\Facades\DB;

class IcdRepository
{
    protected $icd;
    public function __construct(Icd $icd)
    {
        $this->icd = $icd;
    }

    public function applyJoins()
    {
        return $this->icd
            ->select(
                'his_icd.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_icd.icd_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_icd.icd_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_icd.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_icd.' . $key, $item);
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
        return $this->icd->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->icd::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'icd_code' => $request->icd_code,
            'icd_name' => $request->icd_name,
            'icd_name_en' => $request->icd_name_en,
            'icd_name_common' => $request->icd_name_common,
            'icd_group_id' => $request->icd_group_id,
            'attach_icd_codes' => $request->attach_icd_codes,

            'age_from' => $request->age_from,
            'age_to' => $request->age_to,
            'age_type_id' => $request->age_type_id,
            'gender_id' => $request->gender_id,
            'is_sword' => $request->is_sword,
            'is_subcode' => $request->is_subcode,

            'is_latent_tuberculosis' => $request->is_latent_tuberculosis,
            'is_cause' => $request->is_cause,
            'is_hein_nds' => $request->is_hein_nds,
            'is_require_cause' => $request->is_require_cause,
            'is_traditional' => $request->is_traditional,
            'unable_for_treatment' => $request->unable_for_treatment,

            'do_not_use_hein' => $request->do_not_use_hein,
            'is_covid' => $request->is_covid,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            
            'icd_code' => $request->icd_code,
            'icd_name' => $request->icd_name,
            'icd_name_en' => $request->icd_name_en,
            'icd_name_common' => $request->icd_name_common,
            'icd_group_id' => $request->icd_group_id,
            'attach_icd_codes' => $request->attach_icd_codes,

            'age_from' => $request->age_from,
            'age_to' => $request->age_to,
            'age_type_id' => $request->age_type_id,
            'gender_id' => $request->gender_id,
            'is_sword' => $request->is_sword,
            'is_subcode' => $request->is_subcode,

            'is_latent_tuberculosis' => $request->is_latent_tuberculosis,
            'is_cause' => $request->is_cause,
            'is_hein_nds' => $request->is_hein_nds,
            'is_require_cause' => $request->is_require_cause,
            'is_traditional' => $request->is_traditional,
            'unable_for_treatment' => $request->unable_for_treatment,

            'do_not_use_hein' => $request->do_not_use_hein,
            'is_covid' => $request->is_covid,
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
            $data = $data->where('his_icd.id','=', $id)->first();
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