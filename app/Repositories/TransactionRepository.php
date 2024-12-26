<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Transaction;
use App\Models\HIS\TransactionType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionRepository
{
    protected $transaction;
    protected $transactionType;
    protected $transactionTypeTTId;
    public function __construct(
        Transaction $transaction,
        TransactionType $transactionType,
        )
    {
        $this->transaction = $transaction;
        $this->transactionType = $transactionType;

        $this->transactionTypeTTId = Cache::remember('transaction_type_TT_id', now()->addMinutes(10080), function () {
                $data =  $this->transactionType->where('transaction_type_code', 'TT')->get();
                return $data->value('id');
            });
    }

    public function applyJoins()
    {
        return $this->transaction
            ->select(
                'his_transaction.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_transaction.transaction_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_transaction.transaction_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_transaction.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_transaction.' . $key, $item);
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
        return $this->transaction->find($id);
    }
    public function createTransactionPaymentMoMo($payment, $data, $appCreator, $appModifier){
        $data = $this->transaction::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            // 'transaction_code' => $data['orderId'],          
            'transaction_type_id' =>  $this->transactionTypeTTId,
            'transaction_time' => now()->format('Ymdhis'),
            'transaction_date' => now()->format('Ymdhis'),
            'amount' => $data['amount'],  
            'num_order' => $data['transId'],
            'account_book_id' => 32,      
            'pay_form_id' => 2,
            'cashier_room_id' => 1,
            'treatment_id' => $payment->treatment_id,
            'tdl_treatment_code' => $payment->treatment_code,
            'sere_serv_amount' => $data['amount'],
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'transaction_code' => $request->transaction_code,
            'transaction_name' => $request->transaction_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
}