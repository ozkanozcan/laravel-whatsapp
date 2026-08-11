<?php

namespace OzkanOzcan\LaravelWhatsapp\Tests;

use Mockery;
use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappChannelException;
use OzkanOzcan\LaravelWhatsapp\WhatsappBot;
use OzkanOzcan\LaravelWhatsapp\WhatsappChannel;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;
use OzkanOzcan\LaravelWhatsapp\WhatsappServiceProvider;
use Orchestra\Testbench\TestCase;

class WhatsappChannelTest extends TestCase
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
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeNotifiable(string|null $phoneNumber = null): object
    {
        return new class ($phoneNumber) {
            public function __construct(private readonly ?string $phone) {}

            public function routeNotificationFor(string $channel, $notification = null): ?string
            {
                return $this->phone;
            }
        };
    }

    private function makeNotification(WhatsappMessage $message): object
    {
        return new class ($message) {
            public function __construct(private readonly WhatsappMessage $msg) {}

            public function toWhatsapp($notifiable): WhatsappMessage
            {
                return $this->msg;
            }
        };
    }

    public function test_channel_sends_to_notifiable_phone_number(): void
    {
        $message      = WhatsappMessage::create('Hello WhatsApp!');
        $notifiable   = $this->makeNotifiable('+905551234567');
        $notification = $this->makeNotification($message);

        $bot = Mockery::mock(WhatsappBot::class);
        $bot->shouldReceive('sendMessage')
            ->once()
            ->with('+905551234567', $message)
            ->andReturn(['messages' => [['id' => 'wamid.abc123']]]);

        $channel = new WhatsappChannel($bot);
        $result  = $channel->send($notifiable, $notification);

        $this->assertSame(['messages' => [['id' => 'wamid.abc123']]], $result);
    }

    public function test_channel_uses_message_to_override(): void
    {
        // Message has ->to() override: +905559999999
        // Notifiable has different number: +905551234567
        // The message override should take priority
        $message      = WhatsappMessage::create('Hello')->to('+905559999999');
        $notifiable   = $this->makeNotifiable('+905551234567');
        $notification = $this->makeNotification($message);

        $bot = Mockery::mock(WhatsappBot::class);
        $bot->shouldReceive('sendMessage')
            ->once()
            ->with('+905559999999', $message)
            ->andReturn(['messages' => [['id' => 'wamid.override123']]]);

        $channel = new WhatsappChannel($bot);
        $result  = $channel->send($notifiable, $notification);

        $this->assertSame(['messages' => [['id' => 'wamid.override123']]], $result);
    }

    public function test_channel_falls_back_to_config_default(): void
    {
        config(['whatsapp.to' => '+905550000000']);

        $message      = WhatsappMessage::create('Hello');
        $notifiable   = $this->makeNotifiable(null);
        $notification = $this->makeNotification($message);

        $bot = Mockery::mock(WhatsappBot::class);
        $bot->shouldReceive('sendMessage')
            ->once()
            ->with('+905550000000', $message)
            ->andReturn(['messages' => [['id' => 'wamid.config']]]);

        $channel = new WhatsappChannel($bot);
        $result  = $channel->send($notifiable, $notification);

        $this->assertSame(['messages' => [['id' => 'wamid.config']]], $result);
    }

    public function test_channel_throws_when_no_recipient(): void
    {
        $this->expectException(WhatsappChannelException::class);
        $this->expectExceptionMessageMatches('/phone number/');

        config(['whatsapp.to' => '']);

        $message      = WhatsappMessage::create('Hello');
        $notifiable   = $this->makeNotifiable(null);
        $notification = $this->makeNotification($message);

        $bot = Mockery::mock(WhatsappBot::class);
        $bot->shouldNotReceive('sendMessage');

        $channel = new WhatsappChannel($bot);
        $channel->send($notifiable, $notification);
    }
}
