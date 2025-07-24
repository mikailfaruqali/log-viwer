<?php

namespace Snawbar\LogViewer;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class LogViewerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/log-viewer.php',
            'snawbar-log-viewer'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/log-viewer.php' => config_path('snawbar-log-viewer.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/snawbar-log-viewer'),
            ], 'views');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'snawbar-log-viewer');
        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
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
