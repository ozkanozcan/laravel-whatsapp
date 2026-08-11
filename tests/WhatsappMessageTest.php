<?php

namespace OzkanOzcan\LaravelWhatsapp\Tests;

use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;
use PHPUnit\Framework\TestCase;

class WhatsappMessageTest extends TestCase
{
    public function test_create_with_text(): void
    {
        $msg = WhatsappMessage::create('Hello WhatsApp!');

        $this->assertSame('Hello WhatsApp!', $msg->getText());
    }

    public function test_create_empty_then_set_text(): void
    {
        $msg = WhatsappMessage::create()->text('Set later');

        $this->assertSame('Set later', $msg->getText());
    }

    public function test_content_is_alias_for_text(): void
    {
        $msg = WhatsappMessage::create()->content('Via content()');

        $this->assertSame('Via content()', $msg->getText());
    }

    public function test_to_array_text_payload(): void
    {
        $payload = WhatsappMessage::create('Hello')->toArray();

        $this->assertSame('whatsapp', $payload['messaging_product']);
        $this->assertSame('individual', $payload['recipient_type']);
        $this->assertSame('text', $payload['type']);
        $this->assertSame('Hello', $payload['text']['body']);
    }

    public function test_preview_enabled_by_default(): void
    {
        $payload = WhatsappMessage::create('Hello')->toArray();

        $this->assertTrue($payload['text']['preview_url']);
    }

    public function test_preview_can_be_disabled(): void
    {
        $payload = WhatsappMessage::create('Hello')->preview(false)->toArray();

        $this->assertFalse($payload['text']['preview_url']);
    }

    public function test_to_sets_recipient_in_payload(): void
    {
        $msg     = WhatsappMessage::create('Hello')->to('+905551234567');
        $payload = $msg->toArray();

        $this->assertSame('+905551234567', $payload['to']);
        $this->assertSame('+905551234567', $msg->getTo());
    }

    public function test_get_to_returns_null_when_not_set(): void
    {
        $msg = WhatsappMessage::create('Hello');

        $this->assertNull($msg->getTo());
    }

    public function test_is_template_false_by_default(): void
    {
        $msg = WhatsappMessage::create('Hello');

        $this->assertFalse($msg->isTemplate());
    }

    public function test_template_mode_switches_type(): void
    {
        $msg     = WhatsappMessage::create()->template('hello_world', 'en');
        $payload = $msg->toArray();

        $this->assertTrue($msg->isTemplate());
        $this->assertSame('template', $payload['type']);
        $this->assertSame('hello_world', $payload['template']['name']);
        $this->assertSame('en', $payload['template']['language']['code']);
    }

    public function test_template_with_language(): void
    {
        $payload = WhatsappMessage::create()
            ->template('order_shipped', 'tr')
            ->toArray();

        $this->assertSame('tr', $payload['template']['language']['code']);
    }

    public function test_template_with_components(): void
    {
        $components = [
            [
                'type'       => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => '#1234'],
                ],
            ],
        ];

        $payload = WhatsappMessage::create()
            ->template('order_shipped', 'en', $components)
            ->toArray();

        $this->assertArrayHasKey('components', $payload['template']);
        $this->assertCount(1, $payload['template']['components']);
        $this->assertSame('body', $payload['template']['components'][0]['type']);
    }

    public function test_template_without_components_omits_key(): void
    {
        $payload = WhatsappMessage::create()
            ->template('hello_world', 'en')
            ->toArray();

        $this->assertArrayNotHasKey('components', $payload['template']);
    }

    public function test_text_after_template_reverts_to_text_mode(): void
    {
        $msg = WhatsappMessage::create()
            ->template('hello_world', 'en')
            ->text('Back to text');

        $this->assertFalse($msg->isTemplate());
        $this->assertSame('text', $msg->toArray()['type']);
    }
}
