<?php

namespace Alkhwlani\XssMiddleware\Tests;

class XssFilterWithoutAutoRegisterTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_not_filter_xss_as_global_when_auto_reigster_disabled()
    {
        $this->post('add-middleware-auto', $this->uncleanData)->assertJson($this->uncleanData);
    }

    /**
     * @test
     */
    public function it_will_filter_xss_for_specific_route()
    {
        $this->post('add-middleware-manually', $this->uncleanData)->assertJson($this->cleanData);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xss-middleware.auto_register_middleware', false);
    }
}
