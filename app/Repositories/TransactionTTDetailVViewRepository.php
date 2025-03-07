<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\VIEW\TransactionTTDetailVView;
use Illuminate\Support\Facades\DB;

class TransactionTTDetailVViewRepository
{
    protected $transactionTTDetailVView;
    public function __construct(TransactionTTDetailVView $transactionTTDetailVView,)
    {
        $this->transactionTTDetailVView = $transactionTTDetailVView;
    }

    public function applyJoins()
    {
        return $this->transactionTTDetailVView
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
    public function applyBillIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(('bill_id'), $id);
        }
        return $query;
    }
    public function applyBillCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where(('bill_code'), $code);
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
        return $this->transactionTTDetailVView->find($id);
    }

}