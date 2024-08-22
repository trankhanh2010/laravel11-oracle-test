<?php

namespace App\Providers;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Bed\CreateBedIndex;
use App\Events\Elastic\Bed\InsertBedIndex;
use App\Events\Elastic\DeleteIndex;
use App\Listeners\Cache\DeleteCache as CacheDeleteCache;
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
