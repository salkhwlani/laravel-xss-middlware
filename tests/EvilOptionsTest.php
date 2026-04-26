<?php

namespace Alkhwlani\XssMiddleware\Tests;

use PHPUnit\Framework\Attributes\Test;

class EvilOptionsTest extends TestCase
{
    #[Test]
    public function it_strips_extra_evil_attributes_when_configured()
    {
        // `style` is not in voku's default evil-attribute list. Wiring it via
        // the `evil.attributes` config key should make the middleware strip it.
        $payload = ['snippet' => '<p style="color:red">hi</p>'];

        $response = $this->post('add-middleware-auto', $payload)->json('snippet');

        $this->assertStringNotContainsString('style=', $response);
        $this->assertStringContainsString('hi', $response);
    }

    #[Test]
    public function it_strips_extra_evil_html_tags_when_configured()
    {
        // `<svg>` is not in voku's default evil-tag list. With `evil.tags`
        // configured, the inner content is preserved but the tag is stripped.
        $payload = ['snippet' => '<svg>danger</svg>safe'];

        $response = $this->post('add-middleware-auto', $payload)->json('snippet');

        $this->assertStringNotContainsString('<svg', $response);
        $this->assertStringContainsString('safe', $response);
    }

    #[Test]
    public function it_strips_str_afterwards_substrings_when_configured()
    {
        $payload = ['snippet' => 'hello SECRET_TOKEN world'];

        $response = $this->post('add-middleware-auto', $payload)->json('snippet');

        $this->assertStringNotContainsString('SECRET_TOKEN', $response);
        $this->assertStringContainsString('world', $response);
    }

    #[Test]
    public function it_strips_custom_event_handlers_when_configured()
    {
        $payload = ['snippet' => '<a onmycustom="alert(1)">x</a>'];

        $response = $this->post('add-middleware-auto', $payload)->json('snippet');

        $this->assertStringNotContainsString('onmycustom', $response);
    }

    #[Test]
    public function flat_evil_array_still_works_for_old_published_configs()
    {
        // Old graham-campbell shape: bare list of attribute names. Routed onto
        // addEvilAttributes() with a deprecation notice.
        $this->app['config']->set('xss-middleware.evil', ['data-evil']);

        $payload = ['snippet' => '<p data-evil="x">hi</p>'];

        $response = @$this->post('add-middleware-auto', $payload)->json('snippet');

        $this->assertStringNotContainsString('data-evil=', $response);
        $this->assertStringContainsString('hi', $response);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('xss-middleware.evil', [
            'attributes' => ['style'],
            'tags' => ['svg'],
            'events' => ['onmycustom'],
            'strAfterwards' => ['SECRET_TOKEN'],
        ]);
    }
}
