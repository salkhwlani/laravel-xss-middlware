<?php

namespace Alkhwlani\XssMiddleware;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider as ServiceProviderAlias;
use voku\helper\AntiXSS;

class ServiceProvider extends ServiceProviderAlias
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AntiXSS::class, function (Container $app) {
            $antiXss = new AntiXSS;

            $config = $app['config']->get('xss-middleware', []);

            $replacement = $config['replacement'] ?? null;
            if ($replacement !== null) {
                $antiXss->setReplacement($replacement);
            }

            self::applyEvilOptions($antiXss, $config['evil'] ?? null);

            return $antiXss;
        });

        $this->app->singleton(Security::class, fn (Container $app) => new Security($app->make(AntiXSS::class)));

        // BC: graham-campbell/security used to bind `app('security')` transitively.
        // Keep the binding so existing consumers continue to resolve a Security
        // instance with the same `clean()` API.
        $this->app->alias(Security::class, 'security');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
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

    public function registerConfig()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/xss-middleware.php' => config_path('xss-middleware.php'),
            ], 'config');
        }

        $this->mergeConfigFrom(__DIR__.'/../config/xss-middleware.php', 'xss-middleware');
    }

    /**
     * Wire the configured `evil` options onto the AntiXSS instance.
     *
     * Accepts the new associative shape (`['attributes' => [...], 'tags' => [...]]`)
     * and four additional keys mirrored from graham-campbell/security-core:
     * `regex`, `events`, `strAfterwards`, `doNotCloseHtmlTags`.
     *
     * For backward compatibility, a flat list of attribute names is also
     * accepted (graham-campbell shape) and routed to `addEvilAttributes()`,
     * with an `E_USER_DEPRECATED` notice.
     */
    private static function applyEvilOptions(AntiXSS $antiXss, mixed $evil): void
    {
        if (! is_array($evil) || $evil === []) {
            return;
        }

        if (array_is_list($evil)) {
            @trigger_error(
                'xss-middleware: the flat-array shape for the "evil" config key is deprecated. '
                .'Use ["attributes" => [...], "tags" => [...]] instead. The flat shape will be '
                .'removed in v6.',
                E_USER_DEPRECATED
            );
            $antiXss->addEvilAttributes($evil);

            return;
        }

        if (! empty($evil['attributes'])) {
            $antiXss->addEvilAttributes($evil['attributes']);
        }
        if (! empty($evil['tags'])) {
            $antiXss->addEvilHtmlTags($evil['tags']);
        }
        if (! empty($evil['regex'])) {
            $antiXss->addNeverAllowedRegex($evil['regex']);
        }
        if (! empty($evil['events'])) {
            $antiXss->addNeverAllowedOnEventsAfterwards($evil['events']);
        }
        if (! empty($evil['strAfterwards'])) {
            $antiXss->addNeverAllowedStrAfterwards($evil['strAfterwards']);
        }
        if (! empty($evil['doNotCloseHtmlTags'])) {
            $antiXss->addDoNotCloseHtmlTags($evil['doNotCloseHtmlTags']);
        }
    }
}
