# Changelog

All notable changes to `laravel-whatsapp` will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/) and
[Conventional Commits](https://www.conventionalcommits.org/).

---

## [Unreleased]

---

## [1.0.0] — 2024-08-11

### Added
- `WhatsappBot` — main API client for the Meta WhatsApp Business Cloud API
  - `sendMessage()` — send text messages
  - `sendTemplate()` — send pre-approved template messages
  - `sendImage()` — send image messages with optional caption
  - `sendDocument()` — send document messages with optional caption and filename
  - `sendAudio()` — send audio messages
  - `sendVideo()` — send video messages with optional caption
  - `request()` — raw HTTP request to any Meta Graph API endpoint
  - HTTP 429 retry logic with configurable attempts and delay
  - Optional proxy support
  - Optional debug logging via Laravel's log channel
- `WhatsappMessage` — fluent message builder
  - Text message mode with `create()`, `text()`, `content()`
  - Template message mode with `template(name, language, components)`
  - Recipient override with `to()`
  - Link preview control with `preview(bool)`
- `WhatsappChannel` — Laravel notification channel
  - 3-level recipient resolution: message → model routing → config default
- `WhatsappFacade` — `Whatsapp::sendMessage(...)` facade with full `@method` PHPDoc
- `WhatsappServiceProvider` — auto-discovery, singleton binding, publish groups
- `WhatsappTestCommand` — `php artisan whatsapp:test` with `--to` and `--message` options
- `WhatsappApiException` — with helpers: `isRateLimit()`, `isInvalidToken()`, `isInvalidPhoneNumber()`, `isPhoneNumberNotOnWhatsapp()`, `isOutsideMessageWindow()`
- `WhatsappChannelException` — with factory methods: `missingToken()`, `missingPhoneNumberId()`, `missingRecipient()`, `invalidNotifiable()`
- Language files: `en` and `tr`
- GitHub Actions CI: PHP 8.2 / 8.3 / 8.4 × Laravel 10 / 11 / 12 matrix
- Full PHPUnit test suite (WhatsappBotTest, WhatsappMessageTest, WhatsappChannelTest)

[Unreleased]: https://github.com/ozkanozcan/laravel-whatsapp/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/ozkanozcan/laravel-whatsapp/releases/tag/v1.0.0
