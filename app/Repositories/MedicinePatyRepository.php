<?php 
namespace App\Repositories;

use App\Models\HIS\MedicinePaty;
use Illuminate\Support\Facades\DB;

class MedicinePatyRepository
{
    protected $medicinePaty;
    public function __construct(MedicinePaty $medicinePaty)
    {
        $this->medicinePaty = $medicinePaty;
    }

    public function applyJoins()
    {
        return $this->medicinePaty
        ->leftJoin('his_medicine as medicine', 'medicine.id', '=', 'his_medicine_paty.medicine_id')
        ->leftJoin('his_medicine_type as medicine_type', 'medicine_type.id', '=', 'medicine.medicine_type_id')
        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_medicine_paty.patient_type_id')
            ->select(
                'his_medicine_paty.*',
                'medicine_type.medicine_type_code',
                'medicine_type.medicine_type_name',

                'patient_type.patient_type_code',
                'patient_type.patient_type_name',

                'medicine.contract_price',
                'medicine.tax_ratio',
                
                'medicine.expired_date',
                'medicine.tdl_bid_number',
                'medicine.tdl_bid_num_order',

                'medicine.imp_time',
                'medicine.imp_vat_ratio',
                'medicine.imp_price',
                'medicine.vir_imp_price',
                'medicine.internal_price'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_paty.medicine_paty_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_medicine_paty.medicine_paty_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_medicine_paty.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['medicine_type_code', 'medicine_type_name'])) {
                        $query->orderBy('medicine_type.' . $key, $item);
                    }
                    if (in_array($key, ['patient_type_code', 'patient_type_name'])) {
                        $query->orderBy('patient_type.' . $key, $item);
                    }
                    if (in_array($key, [
                        'contract_price',
                        'tax_ratio',
                        'expired_date',
                        'tdl_bid_number',
                        'tdl_bid_num_order',
                        'imp_time',
                        'imp_vat_ratio',
                        'imp_price',
                        'vir_imp_price',
                        'internal_price'
                        ])) {
                        $query->orderBy('medicine.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_medicine_paty.' . $key, $item);
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
        return $this->medicinePaty->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->medicinePaty::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'medicine_id' => $request->medicine_id,
            'patient_type_id' => $request->patient_type_id,
            'exp_price' => $request->exp_price,
            'exp_vat_ratio' => $request->exp_vat_ratio,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'medicine_id' => $request->medicine_id,
            'patient_type_id' => $request->patient_type_id,
            'exp_price' => $request->exp_price,
            'exp_vat_ratio' => $request->exp_vat_ratio,
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
            $data = $data->where('his_medicine_paty.id','=', $id)->first();
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