<?php

namespace App\Listeners\Elastic\TestType;

use App\Events\Elastic\TestType\InsertTestTypeIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertTestTypeIndex
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InsertTestTypeIndex $event): void
    {
        //
    }
}
