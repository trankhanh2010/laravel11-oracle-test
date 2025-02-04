<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\PaymentFeeLockListVView;
use Illuminate\Support\Facades\DB;

class PaymentFeeLockListVViewRepository
{
    protected $paymentFeeLockListVView;
    public function __construct(PaymentFeeLockListVView $paymentFeeLockListVView)
    {
        $this->paymentFeeLockListVView = $paymentFeeLockListVView;
    }

    public function getAll(){
        $data = $this->paymentFeeLockListVView->get();
        return $data;
    }
}