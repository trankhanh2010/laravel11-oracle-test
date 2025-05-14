<?php 
namespace App\Repositories;

use App\Models\VIEW\TransactionTUDetailVView;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionTUDetailVViewRepository
{
    protected $transactionTUDetailVView;
    public function __construct(TransactionTUDetailVView $transactionTUDetailVView,)
    {
        $this->transactionTUDetailVView = $transactionTUDetailVView;
    }

    public function applyJoins()
    {
        return $this->transactionTUDetailVView
            ->select();
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDepositIdFilter($query, $id)
    {
        if ($id != null) {
            $query->where(('deposit_id'), $id);
        }
        return $query;
    }
    public function applyDepositCodeFilter($query, $code)
    {
        if ($code != null) {
            $query->where(('deposit_code'), $code);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('' . $key, $item);
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
        return $this->transactionTUDetailVView->find($id);
    }
    public function applyGroupByField($data, $groupByFields = [])
    {
        if (empty($groupByFields)) {
            return $data;
        }

        // Chuyển các field thành snake_case trước khi nhóm
        $fieldMappings = [];
        foreach ($groupByFields as $field) {
            $snakeField = Str::snake($field);
            $fieldMappings[$snakeField] = $field;
        }
    
        $snakeFields = array_keys($fieldMappings);
    
        // Đệ quy nhóm dữ liệu theo thứ tự fields đã convert
        $groupData = function ($items, $fields) use (&$groupData, $fieldMappings) {
            if (empty($fields)) {
                return $items->values(); // Hết field nhóm -> Trả về danh sách gốc
            }
    
            $currentField = array_shift($fields);
            $originalField = $fieldMappings[$currentField];
    
            return $items->groupBy(function ($item) use ($currentField) {
                return $item[$currentField] ?? null;
            })->map(function ($group, $key) use ($fields, $groupData, $originalField, $currentField) {
                $result = [
                    'key' => (string)$key,
                    $originalField => (string)$key, // Hiển thị tên gốc
                    'total' => $group->count(),
                    'amount' => $group->sum(function ($item) {
                        return (int) $item['amount'] ?? 0;
                    }),
                    'virTotalPrice' => $group->sum(function ($item) {
                        return (int) $item['vir_total_price'] ?? 0;
                    }),
                    'virTotalHeinPrice' => $group->sum(function ($item) {
                        return (int) $item['vir_total_hein_price'] ?? 0;
                    }),
                    'virTotalPatientPrice' => $group->sum(function ($item) {
                        return (int) $item['vir_total_patient_price'] ?? 0;
                    }),
                    // 'children' => $groupData($group, $fields),
                ];
                if ($currentField === 'service_type_name') {
                    $firstItem = $group->first();
                    $result['key'] = $firstItem['service_type_name'].' '.$firstItem['patient_type_name'];
                }

                // Đem children xuống dưới để nằm dưới các trường được thêm
                $result['children'] = $groupData($group, $fields);
                return $result;
            })->values();
        };
    
        return $groupData(collect($data), $snakeFields);
    }

}