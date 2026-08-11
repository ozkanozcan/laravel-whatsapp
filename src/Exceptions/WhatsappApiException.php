<?php

namespace OzkanOzcan\LaravelWhatsapp\Exceptions;

use RuntimeException;

class WhatsappApiException extends RuntimeException
{
    protected int $whatsappErrorCode;

    protected string $whatsappDescription;

    /** @var int|null WhatsApp-specific error sub-code from Meta API response */
    protected ?int $whatsappErrorSubCode;

    public function __construct(
        string $description,
        int $errorCode = 0,
        ?int $errorSubCode = null,
        ?\Throwable $previous = null
    ) {
        $this->whatsappErrorCode    = $errorCode;
        $this->whatsappDescription  = $description;
        $this->whatsappErrorSubCode = $errorSubCode;

        parent::__construct(
            sprintf('WhatsApp API Error [%d]: %s', $errorCode, $description),
            $errorCode,
            $previous
        );
    }

    public function getWhatsappErrorCode(): int
    {
        return $this->whatsappErrorCode;
    }

    public function getWhatsappDescription(): string
    {
        return $this->whatsappDescription;
    }

    public function getWhatsappErrorSubCode(): ?int
    {
        return $this->whatsappErrorSubCode;
    }

    /**
     * HTTP 429 — too many requests.
     */
    public function isRateLimit(): bool
    {
        return $this->whatsappErrorCode === 429;
    }

    /**
     * HTTP 401 — invalid or expired access token.
     */
    public function isInvalidToken(): bool
    {
        return $this->whatsappErrorCode === 401;
    }

    /**
     * Meta error code 130472 — invalid recipient WhatsApp number.
     */
    public function isInvalidPhoneNumber(): bool
    {
        return $this->whatsappErrorCode === 130472;
    }

    /**
     * Meta error code 131030 — recipient phone number not in WhatsApp.
     */
    public function isPhoneNumberNotOnWhatsapp(): bool
    {
        return $this->whatsappErrorCode === 131030;
    }

    /**
     * Message outside of the 24-hour customer-initiated conversation window.
     * Meta error code 131047.
     */
    public function isOutsideMessageWindow(): bool
    {
        return $this->whatsappErrorCode === 131047;
    }
}
