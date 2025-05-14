<?php 
namespace App\Repositories;

use App\Http\Resources\DB\DBVViewResource;
use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TransactionListVView;
use Illuminate\Support\Facades\DB;

class TransactionListVViewRepository
{
    protected $transactionListVView;
    public function __construct(TransactionListVView $transactionListVView)
    {
        $this->transactionListVView = $transactionListVView;
    }

    public function applyJoins()
    {
        return $this->transactionListVView
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
    public function applyTransactionTypeIdsFilter($query, $ids)
    {
        if ($ids != null) {
            $query->whereIn(('transaction_type_id'), $ids);
        }
        return $query;
    }
    public function applyTransactionCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('transaction_code'), $param);
        }
        return $query;
    }
    public function applyTreatmentCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('tdl_treatment_code'), $param);
        }
        return $query;
    }
    public function applyTransReqCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('trans_req_code'), $param);
        }
        return $query;
    }
    public function applyAccountBookCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('account_book_code'), $param);
        }
        return $query;
    }
    public function applyCreateFromTimeFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('create_time', '>=', $param);
            });
        }
        return $query;
    }
    public function applyCreateToTimeFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('create_time', '<=', $param);
            });
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
    public function fetchData($query, $getAll, $start, $limit, $cursorPaginate, $lastId)
    {
        if ($cursorPaginate) {
            $sql = $query->toSql();
            $bindings = $query->getBindings();

            // Thêm các giá trị cần bind vào mảng $bindings
            $bindings[] = $limit + $start; // Giá trị ROWNUM <= ($limit + $start)
            $bindings[] = $lastId;         // Giá trị ID > $lastId
            $bindings[] = $start;          // Giá trị rnum > $start
        
            $fullSql = 'SELECT * FROM (
                            SELECT a.*, ROWNUM rnum 
                            FROM (' . $sql . ') a 
                            WHERE ROWNUM <= ?
                              AND ID > ?
                        ) WHERE rnum > ?';
        
            // Thực hiện truy vấn với các bindings
            $data = DB::connection('oracle_his')->select($fullSql, $bindings);
            $data = DBVViewResource::collection($data);
            return $data;
        }
        
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
        return $this->transactionListVView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->transactionListVView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'transaction_list_v_view_code' => $request->transaction_list_v_view_code,
    //         'transaction_list_v_view_name' => $request->transaction_list_v_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'transaction_list_v_view_code' => $request->transaction_list_v_view_code,
    //         'transaction_list_v_view_name' => $request->transaction_list_v_view_name,
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
            $data = $this->applyJoins()->where('id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('id');
            $maxId = $this->applyJoins()->max('id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('transaction_list_v_view', 'v_his_transaction_list', $startId, $endId, $batchSize);
            }
        }
    }
}