<?php

namespace OzkanOzcan\LaravelWhatsapp;

/**
 * Fluent builder for a WhatsApp Business Cloud API message payload.
 *
 * Usage — text message:
 *   WhatsappMessage::create('Hello World!')
 *       ->to('+905551234567')
 *       ->preview(false);
 *
 * Usage — template message:
 *   WhatsappMessage::create()
 *       ->to('+905551234567')
 *       ->template('order_shipped', 'tr', [
 *           [
 *               'type'       => 'body',
 *               'parameters' => [
 *                   ['type' => 'text', 'text' => '#1234'],
 *               ],
 *           ],
 *       ]);
 */
class WhatsappMessage
{
    /** @var string|null */
    protected ?string $text = null;

    /** @var string|null  Recipient phone number in E.164 format, e.g. +905551234567 */
    protected ?string $to = null;

    /** @var bool Whether to show a link preview in the message */
    protected bool $previewUrl = true;

    /** @var bool Whether the message is a template message */
    protected bool $isTemplate = false;

    /** @var string|null Template name */
    protected ?string $templateName = null;

    /** @var string Template language code */
    protected string $templateLanguage = 'en';

    /** @var array<int, array<string, mixed>> Template components */
    protected array $templateComponents = [];

    /**
     * Create a new WhatsappMessage instance.
     */
    public static function create(string $text = ''): self
    {
        $instance = new self();

        if ($text !== '') {
            $instance->text($text);
        }

        return $instance;
    }

    /**
     * Set the message text body.
     */
    public function text(string $text): self
    {
        $this->text       = $text;
        $this->isTemplate = false;

        return $this;
    }

    /**
     * Set the message text body (alias for text()).
     */
    public function content(string $text): self
    {
        return $this->text($text);
    }

    /**
     * Override the recipient phone number for this message.
     * Must be in E.164 format: +[country_code][number] (e.g. +905551234567)
     */
    public function to(string $phoneNumber): self
    {
        $this->to = $phoneNumber;

        return $this;
    }

    /**
     * Enable or disable link preview in the message.
     * WhatsApp shows link previews by default; pass false to disable.
     */
    public function preview(bool $enable = true): self
    {
        $this->previewUrl = $enable;

        return $this;
    }

    /**
     * Switch the message to template mode.
     *
     * WhatsApp requires approved message templates for:
     * - Messages sent outside the 24-hour customer service window
     * - Business-initiated conversations
     *
     * @param  string  $name        The template name as registered in Meta Business Manager
     * @param  string  $language    BCP-47 language code (e.g. 'en', 'tr', 'en_US')
     * @param  array<int, array<string, mixed>>  $components  Template components (header, body, button parameters)
     */
    public function template(string $name, string $language = 'en', array $components = []): self
    {
        $this->isTemplate         = true;
        $this->templateName       = $name;
        $this->templateLanguage   = $language;
        $this->templateComponents = $components;

        return $this;
    }

    /**
     * Get the recipient phone number override (null = use default).
     */
    public function getTo(): ?string
    {
        return $this->to;
    }

    /**
     * Get the message text.
     */
    public function getText(): ?string
    {
        return $this->text;
    }

    /**
     * Whether this is a template message.
     */
    public function isTemplate(): bool
    {
        return $this->isTemplate;
    }

    /**
     * Build the WhatsApp Business Cloud API payload array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
        ];

        if ($this->to !== null) {
            $payload['to'] = $this->to;
        }

        if ($this->isTemplate) {
            $payload['type']     = 'template';
            $payload['template'] = [
                'name'     => $this->templateName ?? '',
                'language' => ['code' => $this->templateLanguage],
            ];

            if (! empty($this->templateComponents)) {
                $payload['template']['components'] = $this->templateComponents;
            }

            return $payload;
        }

        $payload['type'] = 'text';
        $payload['text'] = [
            'body'        => $this->text ?? '',
            'preview_url' => $this->previewUrl,
        ];

        return $payload;
    }
}
