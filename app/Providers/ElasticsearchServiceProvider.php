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
        try {
            $this->app->singleton('Elasticsearch', function ($app) {
                return ClientBuilder::create()
                    ->setHosts([config('database')['connections']['elasticsearch']['hosts']['host'] . ':' . config('database')['connections']['elasticsearch']['hosts']['port']])
                    ->setBasicAuthentication(config('database')['connections']['elasticsearch']['hosts']['user'], config('database')['connections']['elasticsearch']['hosts']['pass'])
                    ->setCABundle(config('database')['connections']['elasticsearch']['hosts']['ca'])
                    ->build();
            });
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['connection'], $e);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
