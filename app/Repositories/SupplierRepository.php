<?php

namespace App\Repositories;

use App\Models\HIS\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierRepository
{
    protected $supplier;
    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function applyJoins()
    {
        return $this->supplier
            ->select(
                'his_supplier.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_supplier.supplier_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_supplier.supplier_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_supplier.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_supplier.' . $key, $item);
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
        return $this->supplier->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->supplier::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'supplier_code' => $request->supplier_code,
            'supplier_name' => $request->supplier_name,
            'supplier_short_name' => $request->supplier_short_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'tax_code' => $request->tax_code,

            'representative' => $request->representative,
            'position' => $request->position,
            'auth_letter_num' => $request->auth_letter_num,
            'auth_letter_issue_date' => $request->auth_letter_issue_date,
            'contract_num' => $request->contract_num,
            'contract_date' => $request->contract_date,

            'bank_account' => $request->bank_account,
            'fax' => $request->fax,
            'bank_info' => $request->bank_info,
            'address' => $request->address,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'supplier_code' => $request->supplier_code,
            'supplier_name' => $request->supplier_name,
            'supplier_short_name' => $request->supplier_short_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'tax_code' => $request->tax_code,

            'representative' => $request->representative,
            'position' => $request->position,
            'auth_letter_num' => $request->auth_letter_num,
            'auth_letter_issue_date' => $request->auth_letter_issue_date,
            'contract_num' => $request->contract_num,
            'contract_date' => $request->contract_date,

            'bank_account' => $request->bank_account,
            'fax' => $request->fax,
            'bank_info' => $request->bank_info,
            'address' => $request->address,

            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null)
    {
        $data = $this->applyJoins();
        if ($id != null) {
            $data = $data->where('his_supplier.id', '=', $id)->first();
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
