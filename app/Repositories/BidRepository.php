<?php 
namespace App\Repositories;

use App\Models\HIS\Bid;
use Illuminate\Support\Facades\DB;

class BidRepository
{
    protected $bid;
    public function __construct(Bid $bid)
    {
        $this->bid = $bid;
    }

    public function applyJoins()
    {
        return $this->bid
            ->select(
                'his_bid.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_bid.bid_number'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_bid.bid_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_bid.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_bid.' . $key, $item);
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
        return $this->bid->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->bid::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'bid_number'  => $request->bid_number,
            'bid_name' => $request->bid_name,      
            'bid_type_id' => $request->bid_type_id,
            'bid_year' => $request->bid_year,
            'valid_from_time'  => $request->valid_from_time,
            'valid_to_time'  => $request->valid_to_time,
            'allow_update_loginnames' => $request->allow_update_loginnames,
            'approval_time' => $request->approval_time,
            'approval_loginname' => $request->approval_loginname,
            'approval_username'  => $request->approval_username,  
            'bid_extra_code' => $request->bid_extra_code,
            'bid_form_id'  => $request->bid_form_id,
            'bid_apthau_code'  => $request->bid_apthau_code,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'bid_number'  => $request->bid_number,
            'bid_name' => $request->bid_name,      
            'bid_type_id' => $request->bid_type_id,
            'bid_year' => $request->bid_year,
            'valid_from_time'  => $request->valid_from_time,
            'valid_to_time'  => $request->valid_to_time,
            'allow_update_loginnames' => $request->allow_update_loginnames,
            'approval_time' => $request->approval_time,
            'approval_loginname' => $request->approval_loginname,
            'approval_username'  => $request->approval_username,  
            'bid_extra_code' => $request->bid_extra_code,
            'bid_form_id'  => $request->bid_form_id,
            'bid_apthau_code'  => $request->bid_apthau_code,
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
            $data = $data->where('his_bid.id','=', $id)->first();
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