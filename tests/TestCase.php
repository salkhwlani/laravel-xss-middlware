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

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->setUpRoutes();
    }

    protected function setUpRoutes()
    {
        $this->app->get('router')->setRoutes(new RouteCollection);
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
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
