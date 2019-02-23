<?php

namespace Alkhwlani\XssMiddleware\Tests;

class XssFilterTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_filter_xss_as_global_by_default()
    {
        $this->post('add-middleware-auto', $this->uncleanData)->assertJson($this->cleanData);
    }
}
