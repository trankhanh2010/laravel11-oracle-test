<?php

namespace App\Providers;

use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('Elasticsearch', function ($app) {
            return ClientBuilder::create()
            ->setHosts([env('ELASTICSEARCH_HOST').':'.env('ELASTICSEARCH_PORT')])
            ->setBasicAuthentication(env('ELASTICSEARCH_USER'), env('ELASTICSEARCH_PASS'))
            ->setCABundle(env('ELASTICSEARCH_CA'))
            ->build();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
