<?php

namespace OzkanOzcan\LaravelWhatsapp\Tests;

use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappApiException;
use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappChannelException;
use OzkanOzcan\LaravelWhatsapp\WhatsappBot;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;
use OzkanOzcan\LaravelWhatsapp\WhatsappServiceProvider;
use Orchestra\Testbench\TestCase;

class WhatsappBotTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [WhatsappServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('whatsapp.phone_number_id', '1234567890');
        $app['config']->set('whatsapp.access_token', 'test_access_token');
        $app['config']->set('whatsapp.to', '');
        $app['config']->set('whatsapp.api_url', 'https://graph.facebook.com/v20.0');
        $app['config']->set('whatsapp.timeout', 30);
        $app['config']->set('whatsapp.connect_timeout', 10);
        $app['config']->set('whatsapp.retry.times', 0);
        $app['config']->set('whatsapp.retry.sleep_ms', 0);
        $app['config']->set('whatsapp.proxy', null);
        $app['config']->set('whatsapp.logging', false);
    }

    public function test_missing_token_throws_exception(): void
    {
        $this->expectException(WhatsappChannelException::class);
        $this->expectExceptionMessageMatches('/WHATSAPP_ACCESS_TOKEN/');

        $bot = new WhatsappBot(['access_token' => '', 'phone_number_id' => '123']);
        $bot->sendMessage('+905551234567', WhatsappMessage::create('Hello'));
    }

    public function test_missing_phone_number_id_throws_exception(): void
    {
        $this->expectException(WhatsappChannelException::class);
        $this->expectExceptionMessageMatches('/WHATSAPP_PHONE_NUMBER_ID/');

        $bot = new WhatsappBot(['access_token' => 'token123', 'phone_number_id' => '']);
        $bot->sendMessage('+905551234567', WhatsappMessage::create('Hello'));
    }

    public function test_api_exception_helpers(): void
    {
        $e429 = new WhatsappApiException('Too Many Requests', 429);
        $e401 = new WhatsappApiException('Unauthorized', 401);
        $e130472 = new WhatsappApiException('Invalid phone number', 130472);
        $e131030 = new WhatsappApiException('Not on WhatsApp', 131030);
        $e131047 = new WhatsappApiException('Outside window', 131047);

        $this->assertTrue($e429->isRateLimit());
        $this->assertFalse($e429->isInvalidToken());

        $this->assertTrue($e401->isInvalidToken());
        $this->assertFalse($e401->isRateLimit());

        $this->assertTrue($e130472->isInvalidPhoneNumber());
        $this->assertTrue($e131030->isPhoneNumberNotOnWhatsapp());
        $this->assertTrue($e131047->isOutsideMessageWindow());

        $this->assertSame('Too Many Requests', $e429->getWhatsappDescription());
        $this->assertSame(429, $e429->getWhatsappErrorCode());
    }

    public function test_api_exception_sub_code(): void
    {
        $e = new WhatsappApiException('Error with subcode', 130000, 2494010);

        $this->assertSame(2494010, $e->getWhatsappErrorSubCode());
    }

    public function test_api_exception_sub_code_is_null_when_not_provided(): void
    {
        $e = new WhatsappApiException('Error', 400);

        $this->assertNull($e->getWhatsappErrorSubCode());
    }

    public function test_channel_exception_factory_methods(): void
    {
        $e1 = WhatsappChannelException::missingToken();
        $e2 = WhatsappChannelException::missingPhoneNumberId();
        $e3 = WhatsappChannelException::missingRecipient();
        $e4 = WhatsappChannelException::invalidNotifiable('App\\Models\\User');

        $this->assertStringContainsString('WHATSAPP_ACCESS_TOKEN', $e1->getMessage());
        $this->assertStringContainsString('WHATSAPP_PHONE_NUMBER_ID', $e2->getMessage());
        $this->assertStringContainsString('phone number', $e3->getMessage());
        $this->assertStringContainsString('App\\Models\\User', $e4->getMessage());
    }

    public function test_bot_is_resolved_from_container(): void
    {
        $bot = $this->app->make(WhatsappBot::class);

        $this->assertInstanceOf(WhatsappBot::class, $bot);
    }

    public function test_bot_singleton_is_same_instance(): void
    {
        $bot1 = $this->app->make(WhatsappBot::class);
        $bot2 = $this->app->make(WhatsappBot::class);

        $this->assertSame($bot1, $bot2);
    }
}
