<?php

namespace OzkanOzcan\LaravelWhatsapp;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappApiException;
use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappChannelException;

class WhatsappBot
{
    protected string $phoneNumberId;

    protected string $accessToken;

    protected string $apiUrl;

    protected int $timeout;

    protected int $connectTimeout;

    protected int $retryTimes;

    protected int $retrySleepMs;

    protected ?string $proxy;

    protected bool $logging;

    public function __construct(array $config = [])
    {
        $this->phoneNumberId  = $config['phone_number_id'] ?? '';
        $this->accessToken    = $config['access_token'] ?? '';
        $this->apiUrl         = rtrim($config['api_url'] ?? 'https://graph.facebook.com/v20.0', '/');
        $this->timeout        = (int) ($config['timeout'] ?? 30);
        $this->connectTimeout = (int) ($config['connect_timeout'] ?? 10);
        $this->retryTimes     = (int) ($config['retry']['times'] ?? 3);
        $this->retrySleepMs   = (int) ($config['retry']['sleep_ms'] ?? 1000);
        $this->proxy          = $config['proxy'] ?? null;
        $this->logging        = (bool) ($config['logging'] ?? false);
    }

    /**
     * Send a text message to the given WhatsApp phone number.
     *
     * @param  string  $to      Recipient phone number in E.164 format (e.g. +905551234567)
     * @param  string|WhatsappMessage  $message
     * @return array<string, mixed>
     *
     * @throws WhatsappChannelException
     * @throws WhatsappApiException
     */
    public function sendMessage(string $to, string|WhatsappMessage $message): array
    {
        $this->guardCredentials();

        if ($message instanceof WhatsappMessage) {
            $payload = $message->toArray();
        } else {
            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'type'              => 'text',
                'text'              => ['body' => $message, 'preview_url' => false],
            ];
        }

        $payload['to'] = $to;

        if ($this->logging) {
            Log::debug('[LaravelWhatsapp] Sending message', [
                'to'   => $to,
                'type' => $payload['type'] ?? 'text',
                'body' => $payload['text']['body'] ?? ($payload['template']['name'] ?? ''),
            ]);
        }

        return $this->request('POST', $this->messagesEndpoint(), $payload);
    }

    /**
     * Send an approved template message.
     *
     * WhatsApp requires template messages for business-initiated conversations
     * and messages sent outside the 24-hour customer service window.
     *
     * @param  string  $to          Recipient phone number in E.164 format
     * @param  string  $name        Template name as registered in Meta Business Manager
     * @param  string  $language    BCP-47 language code (e.g. 'en', 'tr', 'en_US')
     * @param  array<int, array<string, mixed>>  $components  Template components (header, body, buttons)
     * @return array<string, mixed>
     *
     * @throws WhatsappChannelException
     * @throws WhatsappApiException
     */
    public function sendTemplate(string $to, string $name, string $language = 'en', array $components = []): array
    {
        $this->guardCredentials();

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $to,
            'type'              => 'template',
            'template'          => [
                'name'     => $name,
                'language' => ['code' => $language],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        if ($this->logging) {
            Log::debug('[LaravelWhatsapp] Sending template message', [
                'to'       => $to,
                'template' => $name,
                'language' => $language,
            ]);
        }

        return $this->request('POST', $this->messagesEndpoint(), $payload);
    }

    /**
     * Send an image message.
     *
     * @param  string  $to        Recipient phone number in E.164 format
     * @param  string  $imageUrl  Publicly accessible URL of the image
     * @param  string  $caption   Optional caption text
     * @return array<string, mixed>
     *
     * @throws WhatsappChannelException
     * @throws WhatsappApiException
     */
    public function sendImage(string $to, string $imageUrl, string $caption = ''): array
    {
        $this->guardCredentials();

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'image',
            'image'             => ['link' => $imageUrl],
        ];

        if ($caption !== '') {
            $payload['image']['caption'] = $caption;
        }

        return $this->request('POST', $this->messagesEndpoint(), $payload);
    }

    /**
     * Send a document message.
     *
     * @param  string  $to           Recipient phone number in E.164 format
     * @param  string  $documentUrl  Publicly accessible URL of the document
     * @param  string  $caption      Optional caption text
     * @param  string  $filename     Optional filename shown to the recipient
     * @return array<string, mixed>
     *
     * @throws WhatsappChannelException
     * @throws WhatsappApiException
     */
    public function sendDocument(
        string $to,
        string $documentUrl,
        string $caption = '',
        string $filename = ''
    ): array {
        $this->guardCredentials();

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'document',
            'document'          => ['link' => $documentUrl],
        ];

        if ($caption !== '') {
            $payload['document']['caption'] = $caption;
        }

        if ($filename !== '') {
            $payload['document']['filename'] = $filename;
        }

        return $this->request('POST', $this->messagesEndpoint(), $payload);
    }

    /**
     * Send an audio message.
     *
     * @param  string  $to        Recipient phone number in E.164 format
     * @param  string  $audioUrl  Publicly accessible URL of the audio file
     * @return array<string, mixed>
     *
     * @throws WhatsappChannelException
     * @throws WhatsappApiException
     */
    public function sendAudio(string $to, string $audioUrl): array
    {
        $this->guardCredentials();

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'audio',
            'audio'             => ['link' => $audioUrl],
        ];

        return $this->request('POST', $this->messagesEndpoint(), $payload);
    }

    /**
     * Send a video message.
     *
     * @param  string  $to        Recipient phone number in E.164 format
     * @param  string  $videoUrl  Publicly accessible URL of the video file
     * @param  string  $caption   Optional caption text
     * @return array<string, mixed>
     *
     * @throws WhatsappChannelException
     * @throws WhatsappApiException
     */
    public function sendVideo(string $to, string $videoUrl, string $caption = ''): array
    {
        $this->guardCredentials();

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $to,
            'type'              => 'video',
            'video'             => ['link' => $videoUrl],
        ];

        if ($caption !== '') {
            $payload['video']['caption'] = $caption;
        }

        return $this->request('POST', $this->messagesEndpoint(), $payload);
    }

    /**
     * Make a raw HTTP request to any Meta Graph API endpoint.
     *
     * @param  string  $method    HTTP method: 'POST' or 'GET'
     * @param  string  $endpoint  Full URL of the endpoint
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws WhatsappApiException
     */
    public function request(string $method, string $endpoint, array $payload = []): array
    {
        $attempt = 0;

        do {
            $http = Http::timeout($this->timeout)
                ->connectTimeout($this->connectTimeout)
                ->withToken($this->accessToken);

            if ($this->proxy) {
                $http = $http->withOptions(['proxy' => $this->proxy]);
            }

            $response = strtoupper($method) === 'GET'
                ? $http->get($endpoint, $payload)
                : $http->post($endpoint, $payload);

            $body = $response->json() ?? [];

            if ($response->successful() && ! isset($body['error'])) {
                return $body;
            }

            // Extract Meta error details
            $error       = $body['error'] ?? [];
            $errorCode   = (int) ($error['code'] ?? $response->status());
            $description = (string) ($error['message'] ?? 'Unknown WhatsApp API error');
            $subCode     = isset($error['error_subcode']) ? (int) $error['error_subcode'] : null;

            // Retry on rate limit (HTTP 429)
            if ($errorCode === 429 && $attempt < $this->retryTimes) {
                usleep($this->retrySleepMs * 1_000);
                $attempt++;

                continue;
            }

            throw new WhatsappApiException($description, $errorCode, $subCode);
        } while ($attempt <= $this->retryTimes);

        throw new WhatsappApiException('Max retry attempts reached.', 429);
    }

    /**
     * Get the messages endpoint URL for the configured phone number.
     */
    protected function messagesEndpoint(): string
    {
        return "{$this->apiUrl}/{$this->phoneNumberId}/messages";
    }

    /**
     * Guard against missing credentials before making API calls.
     *
     * @throws WhatsappChannelException
     */
    protected function guardCredentials(): void
    {
        if (empty($this->accessToken)) {
            throw WhatsappChannelException::missingToken();
        }

        if (empty($this->phoneNumberId)) {
            throw WhatsappChannelException::missingPhoneNumberId();
        }
    }
}
