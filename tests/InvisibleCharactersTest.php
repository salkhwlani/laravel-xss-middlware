<?php

namespace Alkhwlani\XssMiddleware\Tests;

use PHPUnit\Framework\Attributes\Test;

class InvisibleCharactersTest extends TestCase
{
    #[Test]
    public function it_strips_c0_control_characters_from_clean_input()
    {
        // \x01 SOH, \x07 BEL, \x1b ESC. voku::xss_clean leaves these alone
        // when no XSS is detected; the middleware strips them as hardening
        // (null-byte injection, log poisoning, terminal escapes).
        $payload = ['username' => "ali\x01\x07\x1bce"];

        $this->post('add-middleware-auto', $payload)
            ->assertJson(['username' => 'alice']);
    }

    #[Test]
    public function it_strips_url_encoded_c0_control_characters()
    {
        // %01 is the URL-encoded form of \x01. The second `true` arg to
        // UTF8::remove_invisible_characters handles this variant too.
        $payload = ['username' => 'al%01ice'];

        $this->post('add-middleware-auto', $payload)
            ->assertJson(['username' => 'alice']);
    }

    #[Test]
    public function it_preserves_tab_newline_and_carriage_return()
    {
        // These three are explicitly kept by remove_invisible_characters.
        $payload = ['snippet' => "line1\nline2\tindented\r\nline3"];

        $this->post('add-middleware-auto', $payload)
            ->assertJson(['snippet' => "line1\nline2\tindented\r\nline3"]);
    }

    #[Test]
    public function it_re_runs_xss_clean_when_invisible_strip_changes_the_string()
    {
        // Hide an event handler behind a C0 byte. Plain xss_clean wouldn't
        // detect it because the bytes break the attribute parser, but after
        // stripping invisibles a second xss_clean pass catches it.
        $payload = ['snippet' => '<a on'."\x01".'click="alert(1)">x</a>'];

        $response = $this->post('add-middleware-auto', $payload)->json('snippet');

        $this->assertStringNotContainsString('alert', $response);
        $this->assertStringNotContainsString("\x01", $response);
    }
}
