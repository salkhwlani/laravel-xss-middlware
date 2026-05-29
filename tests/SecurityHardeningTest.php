<?php

namespace Alkhwlani\XssMiddleware\Tests;

use Alkhwlani\XssMiddleware\Security;
use PHPUnit\Framework\Attributes\Test;

class SecurityHardeningTest extends TestCase
{
    #[Test]
    public function it_still_filters_xss_when_except_config_is_null()
    {
        $this->app['config']->set('xss-middleware.except', null);

        $this->post('add-middleware-auto', $this->uncleanData)
            ->assertJson($this->cleanData);
    }

    #[Test]
    public function it_still_filters_xss_when_except_config_key_is_missing()
    {
        $config = $this->app['config']->get('xss-middleware');
        unset($config['except']);
        $this->app['config']->set('xss-middleware', $config);

        $this->post('add-middleware-auto', $this->uncleanData)
            ->assertJson($this->cleanData);
    }

    #[Test]
    public function it_uses_strict_comparison_so_invisible_chars_trigger_second_pass()
    {
        $security = $this->app->make(Security::class);

        // A payload hiding an event handler behind a C0 byte. The strict
        // comparison ensures the second xss_clean pass is triggered after
        // stripping invisible characters.
        $result = $security->clean('<a on'."\x01".'click="alert(1)">x</a>');

        $this->assertStringNotContainsString('alert', $result);
        $this->assertStringNotContainsString("\x01", $result);
    }

    #[Test]
    public function it_falls_back_to_default_middleware_when_configured_class_does_not_exist()
    {
        $this->app['config']->set('xss-middleware.middleware', 'App\\NonExistent\\Middleware');

        // Re-register the service provider so it picks up the bad config.
        $this->app->register(\Alkhwlani\XssMiddleware\ServiceProvider::class, true);

        $this->post('add-middleware-manually', $this->uncleanData)
            ->assertJson($this->cleanData);
    }

    #[Test]
    public function it_falls_back_to_default_middleware_when_configured_class_is_not_a_string()
    {
        $this->app['config']->set('xss-middleware.middleware', 12345);

        $this->app->register(\Alkhwlani\XssMiddleware\ServiceProvider::class, true);

        $this->post('add-middleware-manually', $this->uncleanData)
            ->assertJson($this->cleanData);
    }

    #[Test]
    public function it_boots_correctly_with_base64_app_key()
    {
        $key = $this->app['config']->get('app.key');

        $this->assertStringStartsWith('base64:', $key);
        $this->assertEquals(32, strlen(base64_decode(substr($key, 7))));
    }
}
