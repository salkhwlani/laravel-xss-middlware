<?php

namespace Alkhwlani\XssMiddleware\Tests;

use Alkhwlani\XssMiddleware\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $uncleanData = [
        'username' => '<a onclick="">test xss</a>',
        'deep-array' => [
            'username' => '<a onclick="">test xss</a>',
        ],
    ];

    protected $cleanData = [
        'username' => '<a >test xss</a>',
        'deep-array' => [
            'username' => '<a >test xss</a>',
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->artisan('migrate', ['--database' => 'testing']);
        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__.'/../src/database/factories');
        $this->setUpRoutes();
    }

    /**
     * refresh (reboot) app.
     *
     * because auto register middleware as global happen when boot app so if we need disabled it we need reboot app also
     */
    protected function refreshApp(): void
    {
        $this->app = false;
        $this->setUp();
    }

    protected function setUpRoutes()
    {
        $this->app->get('router')->setRoutes(new RouteCollection());
        $this->app->get('router')->any('/add-middleware-auto', [$this->responseRequest()]);
        $this->app->get('router')->any('/add-middleware-manually', [
            'middleware' => 'xss-filter', $this->responseRequest(),
        ]);
    }

    /**
     * @return \Closure
     */
    protected function responseRequest()
    {
        return function (Request $request) {
            return response()->json($request, 200, [], JSON_UNESCAPED_UNICODE);
        };
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [\GrahamCampbell\Security\SecurityServiceProvider::class, ServiceProvider::class];
    }
}
