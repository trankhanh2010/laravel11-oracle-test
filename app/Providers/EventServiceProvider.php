<?php

namespace App\Providers;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\CreateAccidentBodyPartIndex;
use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\CreateAccidentCareIndex;
use App\Events\Elastic\AccidentCare\InsertAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\CreateAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentHurtType\InsertAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\CreateAccidentLocationIndex;
use App\Events\Elastic\AccidentLocation\InsertAccidentLocationIndex;
use App\Events\Elastic\Bed\CreateBedIndex;
use App\Events\Elastic\Bed\InsertBedIndex;
use App\Events\Elastic\DeleteIndex;
use App\Listeners\Cache\DeleteCache as CacheDeleteCache;
use App\Listeners\Elastic\AccidentBodyPart\ElasticCreateAccidentBodyPartIndex;
use App\Listeners\Elastic\AccidentBodyPart\ElasticInsertAccidentBodyPartIndex;
use App\Listeners\Elastic\AccidentCare\ElasticCreateAccidentCareIndex;
use App\Listeners\Elastic\AccidentCare\ElasticInsertAccidentCareIndex;
use App\Listeners\Elastic\AccidentHurtType\ElasticCreateAccidentHurtTypeIndex;
use App\Listeners\Elastic\AccidentHurtType\ElasticInsertAccidentHurtTypeIndex;
use App\Listeners\Elastic\AccidentLocation\ElasticCreateAccidentLocationIndex;
use App\Listeners\Elastic\AccidentLocation\ElasticInsertAccidentLocationIndex;
use App\Listeners\Elastic\Bed\ElasticCreateBedIndex;
use App\Listeners\Elastic\Bed\ElasticInsertBedIndex;
use App\Listeners\Elastic\ElasticDeleteIndex;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Cache
        DeleteCache::class => [
            CacheDeleteCache::class,
        ],

        // Elastic Search
        DeleteIndex::class => [
            ElasticDeleteIndex::class,
        ],

        CreateAccidentBodyPartIndex::class => [
            ElasticCreateAccidentBodyPartIndex::class,
        ],
        InsertAccidentBodyPartIndex::class => [
            ElasticInsertAccidentBodyPartIndex::class,
        ],
        CreateAccidentCareIndex::class => [
            ElasticCreateAccidentCareIndex::class,
        ],
        InsertAccidentCareIndex::class => [
            ElasticInsertAccidentCareIndex::class,
        ],
        CreateAccidentHurtTypeIndex::class => [
            ElasticCreateAccidentHurtTypeIndex::class,
        ],
        InsertAccidentHurtTypeIndex::class => [
            ElasticInsertAccidentHurtTypeIndex::class,
        ],
        CreateAccidentLocationIndex::class => [
            ElasticCreateAccidentLocationIndex::class,
        ],
        InsertAccidentLocationIndex::class => [
            ElasticInsertAccidentLocationIndex::class,
        ],
        CreateBedIndex::class => [
            ElasticCreateBedIndex::class,
        ],
        InsertBedIndex::class => [
            ElasticInsertBedIndex::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
