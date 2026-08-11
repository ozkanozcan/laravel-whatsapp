<?php

namespace OzkanOzcan\LaravelWhatsapp;

use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappChannelException;

class WhatsappChannel
{
    public function __construct(protected WhatsappBot $bot) {}

    /**
     * Send the given notification via WhatsApp.
     *
     * Recipient resolution priority (same pattern as laravel-telegram):
     *   1. $message->getTo()                                    — message-level override
     *   2. $notifiable->routeNotificationFor('whatsapp', ...)   — model routing
     *   3. config('whatsapp.to')                                — application default
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification|mixed  $notification
     * @return array<string, mixed>|null
     *
     * @throws WhatsappChannelException
     */
    public function send(mixed $notifiable, mixed $notification): ?array
    {
        /** @var WhatsappMessage $message */
        $message = $notification->toWhatsapp($notifiable);

        // Resolve recipient: message override → routeNotificationForWhatsapp() → config default
        $to = $message->getTo()
            ?? $notifiable->routeNotificationFor('whatsapp', $notification)
            ?? config('whatsapp.to');

        if (empty($to)) {
            throw WhatsappChannelException::missingRecipient();
        }

        return $this->bot->sendMessage((string) $to, $message);
    }
}
