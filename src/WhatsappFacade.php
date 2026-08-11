<?php

namespace OzkanOzcan\LaravelWhatsapp;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, mixed> sendMessage(string $to, string|WhatsappMessage $message)
 * @method static array<string, mixed> sendTemplate(string $to, string $name, string $language = 'en', array $components = [])
 * @method static array<string, mixed> sendImage(string $to, string $imageUrl, string $caption = '')
 * @method static array<string, mixed> sendDocument(string $to, string $documentUrl, string $caption = '', string $filename = '')
 * @method static array<string, mixed> sendAudio(string $to, string $audioUrl)
 * @method static array<string, mixed> sendVideo(string $to, string $videoUrl, string $caption = '')
 * @method static array<string, mixed> request(string $method, string $endpoint, array $payload = [])
 *
 * @see \OzkanOzcan\LaravelWhatsapp\WhatsappBot
 */
class WhatsappFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WhatsappBot::class;
    }
}
