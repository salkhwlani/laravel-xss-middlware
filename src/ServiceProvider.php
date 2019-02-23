<?php

namespace Alkhwlani\XssMiddleware;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider as ServiceProviderAlias;

class ServiceProvider extends ServiceProviderAlias
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMiddleware();
    }

    /**
     * auto append middleware to router.
     */
    protected function registerMiddleware()
    {
        $middlewareClass = $this->app['config']->get('xss-middleware.middleware', XSSFilterMiddleware::class);

        $this->app['router']->aliasMiddleware('xss-filter', $middlewareClass);

        $registerType = $this->app['config']->get('xss-middleware.auto_register_middleware', false);

        if ($registerType === false) {
            return;
        }

        if ($registerType === true) { // Register middleware as global Middleware
            $this->app->make(Kernel::class)->pushMiddleware($middlewareClass);

            return;
        }

        // Register Middleware for route group
        foreach (Arr::wrap($registerType) as $group) {
            $this->app['router']->pushMiddlewareToGroup($group, $middlewareClass);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    public function registerConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/xss-middleware.php' => config_path('xss-middleware.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/xss-middleware.php', 'xss-middleware');
    }
}