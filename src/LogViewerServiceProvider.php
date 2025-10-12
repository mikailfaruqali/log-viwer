<?php

namespace Snawbar\LogViewer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LogViewerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            sprintf('%s/../config/log-viewer.php', __DIR__),
            'snawbar-log-viewer'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                sprintf('%s/../config/log-viewer.php', __DIR__) => config_path('snawbar-log-viewer.php'),
            ], 'config');

            $this->publishes([
                sprintf('%s/../resources/views', __DIR__) => resource_path('views/vendor/snawbar-log-viewer'),
            ], 'views');
        }

        $this->loadViewsFrom(sprintf('%s/../resources/views', __DIR__), 'snawbar-log-viewer');
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(sprintf('%s/routes/web.php', __DIR__));
        });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix' => config('snawbar-log-viewer.route-path'),
            'middleware' => config('snawbar-log-viewer.middleware'),
        ];
    }
}
