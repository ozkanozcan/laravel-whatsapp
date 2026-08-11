<?php

namespace OzkanOzcan\LaravelWhatsapp\Exceptions;

use RuntimeException;

class WhatsappChannelException extends RuntimeException
{
    /**
     * Thrown when the access token is empty or not configured.
     */
    public static function missingToken(): self
    {
        return new self(
            'WhatsApp access token is not configured. ' .
            'Set WHATSAPP_ACCESS_TOKEN in your .env file.'
        );
    }

    /**
     * Thrown when the phone_number_id is empty or not configured.
     */
    public static function missingPhoneNumberId(): self
    {
        return new self(
            'WhatsApp Phone Number ID is not configured. ' .
            'Set WHATSAPP_PHONE_NUMBER_ID in your .env file. ' .
            'You can find this in Meta Developers → Your App → WhatsApp → API Setup.'
        );
    }

    /**
     * Thrown when no recipient phone number can be resolved.
     */
    public static function missingRecipient(): self
    {
        return new self(
            'No WhatsApp recipient phone number provided. ' .
            'Implement routeNotificationForWhatsapp() on your notifiable model ' .
            'or set WHATSAPP_TO in your .env file.'
        );
    }

    /**
     * Thrown when the notifiable does not implement the routing method.
     */
    public static function invalidNotifiable(string $class): self
    {
        return new self(
            sprintf(
                'Notifiable [%s] does not have a routeNotificationForWhatsapp() method.',
                $class
            )
        );
    }
}
