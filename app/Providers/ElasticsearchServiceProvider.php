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
                // Lấy danh sách hosts từ biến môi trường
                $hosts = explode(',', config('database')['connections']['elasticsearch']['hosts']['host']);
                
                return ClientBuilder::create()
                    ->setHosts($hosts) // Sử dụng danh sách hosts
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
