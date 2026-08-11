# Laravel WhatsApp Notifier

[![Tests](https://github.com/ozkanozcan/laravel-whatsapp/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/ozkanozcan/laravel-whatsapp/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/ozkanozcan/laravel-whatsapp.svg)](https://packagist.org/packages/ozkanozcan/laravel-whatsapp)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/laravel-10--13-red)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A clean, zero-dependency WhatsApp Business notification channel for **Laravel 10, 11, 12, and 13**.  
Send text messages, approved templates, images, documents, audio, and video directly from your Laravel application via the [WhatsApp Business Cloud API](https://developers.facebook.com/docs/whatsapp/cloud-api).

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Step 1 — Create a Meta Developer App](#step-1--create-a-meta-developer-app)
- [Step 2 — Get Your Phone Number ID & Access Token](#step-2--get-your-phone-number-id--access-token)
- [Step 3 — Configure the Package](#step-3--configure-the-package)
- [Basic Usage](#basic-usage)
- [Laravel Notifications](#laravel-notifications)
- [Facade Usage](#facade-usage)
- [WhatsappMessage Reference](#whatsappmessage-reference)
- [Template Messages](#template-messages)
- [Media Messages](#media-messages)
- [Artisan Command](#artisan-command)
- [Language Files](#language-files)
- [Configuration Reference](#configuration-reference)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [License](#license)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.2 |
| Laravel | 10.x / 11.x / 12.x / 13.x |
| Meta WhatsApp Business Account | Required |

---

## Installation

Install the package via Composer:

```bash
composer require ozkanozcan/laravel-whatsapp
```

Laravel's auto-discovery will register the service provider and `Whatsapp` facade automatically.

**Publish the configuration file:**

```bash
php artisan vendor:publish --tag=whatsapp-config
```

**Publish language files** (optional — required only to customise messages):

```bash
php artisan vendor:publish --tag=whatsapp-lang
```

---

## Step 1 — Create a Meta Developer App

1. Go to [Meta for Developers](https://developers.facebook.com/) and log in.
2. Click **My Apps → Create App**.
3. Select **Business** as the app type.
4. Enter a name and click **Create App**.
5. On the app dashboard, click **Add Product** and select **WhatsApp**.
6. Click **Set up** on the WhatsApp product card.

---

## Step 2 — Get Your Phone Number ID & Access Token

### Phone Number ID

1. In your app dashboard, navigate to **WhatsApp → API Setup**.
2. You will see a **Phone number ID** under the "From" section — copy this value.
   > This is a numeric ID (e.g. `123456789012345`), **not** the actual phone number.

Add it to your `.env`:
```env
WHATSAPP_PHONE_NUMBER_ID=123456789012345
```

### Access Token

**For development (temporary token — expires in 24 hours):**

On the **API Setup** page, you will find a temporary access token. Copy it for quick testing.

**For production (permanent System User token — recommended):**

1. Go to [Meta Business Manager](https://business.facebook.com/).
2. Navigate to **Settings → System Users**.
3. Create a **System User** and assign the WhatsApp app with `whatsapp_business_messaging` permission.
4. Click **Generate Token** and copy the token.

Add it to your `.env`:
```env
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Default Recipient (optional)

Set a default phone number to receive messages when no specific recipient is configured:

```env
WHATSAPP_TO=+905551234567
```

> **Important:** Phone numbers must be in **E.164 format**: `+[country_code][number]` (e.g. `+905551234567`). No spaces, dashes, or parentheses.

---

## Step 3 — Configure the Package

Your `.env` file should contain at minimum:

```env
WHATSAPP_PHONE_NUMBER_ID=123456789012345
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
WHATSAPP_TO=+905551234567
```

**Verify your setup** with the built-in Artisan command:

```bash
php artisan whatsapp:test
```

---

## Basic Usage

### Send a simple text message

```php
use OzkanOzcan\LaravelWhatsapp\WhatsappBot;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;

$bot = app(WhatsappBot::class);

$bot->sendMessage(
    '+905551234567',
    WhatsappMessage::create('Hello from Laravel! 🚀')->preview(false)
);
```

### Send a plain string

```php
$bot->sendMessage('+905551234567', 'Hello World!');
```

---

## Laravel Notifications

This is the recommended way to use the package in Laravel applications.

### 1. Add routing to your notifiable model

```php
// app/Models/User.php

use OzkanOzcan\LaravelWhatsapp\WhatsappChannel;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;

class User extends Authenticatable
{
    public function routeNotificationForWhatsapp(): ?string
    {
        // Return the user's WhatsApp phone number stored in the database,
        // or fall back to the application-wide default.
        return $this->whatsapp_phone ?? config('whatsapp.to');
    }
}
```

### 2. Create a notification class

```bash
php artisan make:notification OrderShipped
```

```php
// app/Notifications/OrderShipped.php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use OzkanOzcan\LaravelWhatsapp\WhatsappChannel;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;

class OrderShipped extends Notification
{
    public function __construct(private readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return [WhatsappChannel::class];
    }

    public function toWhatsapp(object $notifiable): WhatsappMessage
    {
        return WhatsappMessage::create(
            "📦 *Order Shipped!*\n\n" .
            "Order: #{$this->order->id}\n" .
            "Customer: {$this->order->customer_name}\n" .
            "Total: \${$this->order->total}"
        )->preview(false);
    }
}
```

### 3. Dispatch the notification

```php
// Send to a specific user
$user->notify(new OrderShipped($order));

// Send to multiple users
Notification::send(User::all(), new OrderShipped($order));
```

---

## Facade Usage

The `Whatsapp` facade is available after auto-discovery:

```php
use OzkanOzcan\LaravelWhatsapp\WhatsappFacade as Whatsapp;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;

// Send a text message
Whatsapp::sendMessage(
    '+905551234567',
    WhatsappMessage::create('🔔 New user registered!')->preview(false)
);

// Send a template message
Whatsapp::sendTemplate(
    '+905551234567',
    'order_shipped',
    'tr',
    [
        [
            'type'       => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => '#1234'],
            ],
        ],
    ]
);

// Send an image
Whatsapp::sendImage(
    '+905551234567',
    'https://example.com/photo.jpg',
    'Check out this photo!'
);

// Send a document
Whatsapp::sendDocument(
    '+905551234567',
    'https://example.com/report.pdf',
    'Monthly Report',
    'monthly-report-2024.pdf'
);

// Send audio
Whatsapp::sendAudio('+905551234567', 'https://example.com/voice.mp3');

// Send video
Whatsapp::sendVideo('+905551234567', 'https://example.com/video.mp4', 'Product demo');

// Raw API call
Whatsapp::request('POST', 'https://graph.facebook.com/v20.0/PHONE_ID/messages', [
    'messaging_product' => 'whatsapp',
    'to'                => '+905551234567',
    'type'              => 'text',
    'text'              => ['body' => 'Hello!'],
]);
```

---

## WhatsappMessage Reference

```php
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;

$message = WhatsappMessage::create('Your text here')

    // ── Content ──────────────────────────────────────
    ->text('Override text')             // Set message body
    ->content('Alias for text()')       // Alias

    // ── Behaviour ────────────────────────────────────
    ->preview(false)                    // Disable link preview (default: enabled)

    // ── Recipient override ───────────────────────────
    ->to('+905551234567')               // Override recipient for this message

    // ── Template mode ────────────────────────────────
    ->template('template_name', 'tr', $components); // Switch to template message
```

---

## Template Messages

WhatsApp enforces a **24-hour customer service window**: you can only send free-form text messages to users who messaged you within the last 24 hours. Outside that window, you **must** use a pre-approved message template.

### Creating a Template

1. Go to [Meta Business Manager](https://business.facebook.com/) → **WhatsApp Manager → Message Templates**.
2. Click **Create Template** and follow the approval process.
3. Once approved, use the template name in your code.

### Sending a Template Message

```php
// Simple template with no variables
WhatsappMessage::create()->template('hello_world', 'en');

// Template with body parameters
WhatsappMessage::create()->template('order_shipped', 'tr', [
    [
        'type'       => 'body',
        'parameters' => [
            ['type' => 'text', 'text' => '#1234'],
            ['type' => 'text', 'text' => 'John Doe'],
        ],
    ],
]);

// Template with header image + body + buttons
WhatsappMessage::create()->template('promo_offer', 'en', [
    [
        'type'      => 'header',
        'parameters' => [
            ['type' => 'image', 'image' => ['link' => 'https://example.com/promo.jpg']],
        ],
    ],
    [
        'type'       => 'body',
        'parameters' => [
            ['type' => 'text', 'text' => '50%'],
        ],
    ],
    [
        'type'    => 'button',
        'sub_type' => 'quick_reply',
        'index'   => '0',
        'parameters' => [
            ['type' => 'payload', 'payload' => 'PROMO_YES'],
        ],
    ],
]);
```

---

## Media Messages

```php
use OzkanOzcan\LaravelWhatsapp\WhatsappFacade as Whatsapp;

// Image
Whatsapp::sendImage('+905551234567', 'https://example.com/image.jpg', 'Optional caption');

// Document
Whatsapp::sendDocument(
    '+905551234567',
    'https://example.com/file.pdf',
    'Invoice',
    'invoice-2024-001.pdf'
);

// Audio
Whatsapp::sendAudio('+905551234567', 'https://example.com/audio.mp3');

// Video
Whatsapp::sendVideo('+905551234567', 'https://example.com/video.mp4', 'Product walkthrough');
```

> **Note:** Media files must be hosted at a **publicly accessible HTTPS URL**. Alternatively, you can upload them to the [WhatsApp Media API](https://developers.facebook.com/docs/whatsapp/cloud-api/reference/media) and use the returned media ID instead of a URL.

---

## Artisan Command

Test your WhatsApp configuration without writing any code:

```bash
# Basic test — sends to WHATSAPP_TO from config
php artisan whatsapp:test

# Override recipient phone number
php artisan whatsapp:test --to=+905551234567

# Send a custom message
php artisan whatsapp:test --message="Hello from artisan!"
```

---

## Language Files

The package ships with `en` and `tr` language files.

Publish them to customise or add new locales:

```bash
php artisan vendor:publish --tag=whatsapp-lang
```

Files will be placed at `lang/vendor/whatsapp/{locale}/whatsapp.php`.

To use translations in your own code:

```php
trans('whatsapp::whatsapp.missing_token');
trans('whatsapp::whatsapp.api_error', ['code' => 429, 'description' => 'Too Many Requests']);
```

To add a new locale (e.g. German), create:

```
lang/vendor/whatsapp/de/whatsapp.php
```

and add the same keys as the `en` file.

---

## Configuration Reference

After publishing the config (`php artisan vendor:publish --tag=whatsapp-config`), you can fine-tune `config/whatsapp.php`:

| Key | Default | Description |
|---|---|---|
| `phone_number_id` | `''` | Phone Number ID from Meta Business Platform |
| `access_token` | `''` | Permanent System User or temporary access token |
| `to` | `''` | Default recipient phone number (E.164 format) |
| `api_url` | Meta Graph API v20.0 | Override for custom API versions |
| `timeout` | `30` | HTTP read timeout (seconds) |
| `connect_timeout` | `10` | HTTP connect timeout (seconds) |
| `retry.times` | `3` | Retry count on HTTP 429 |
| `retry.sleep_ms` | `1000` | Delay between retries (ms) |
| `proxy` | `null` | HTTP proxy URL |
| `logging` | `false` | Log every sent message |

---

## Error Handling

```php
use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappApiException;
use OzkanOzcan\LaravelWhatsapp\Exceptions\WhatsappChannelException;
use OzkanOzcan\LaravelWhatsapp\WhatsappFacade as Whatsapp;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;

try {
    Whatsapp::sendMessage(
        '+905551234567',
        WhatsappMessage::create('Hello!')->preview(false)
    );
} catch (WhatsappChannelException $e) {
    // Configuration error (missing token, missing phone_number_id, missing recipient)
    logger()->error('WhatsApp config error: ' . $e->getMessage());

} catch (WhatsappApiException $e) {
    if ($e->isRateLimit()) {
        // HTTP 429 — sending too many messages
        logger()->warning('WhatsApp rate limit hit.');

    } elseif ($e->isInvalidToken()) {
        // HTTP 401 — token is invalid or expired
        logger()->error('WhatsApp token is invalid. Regenerate from Meta Business Manager.');

    } elseif ($e->isInvalidPhoneNumber()) {
        // Error 130472 — recipient number is not valid
        logger()->warning('Invalid WhatsApp phone number.');

    } elseif ($e->isPhoneNumberNotOnWhatsapp()) {
        // Error 131030 — number is valid but not registered on WhatsApp
        logger()->warning('Phone number is not on WhatsApp.');

    } elseif ($e->isOutsideMessageWindow()) {
        // Error 131047 — 24-hour window expired, use a template instead
        logger()->warning('WhatsApp 24-hour window expired. Sending template instead...');
        Whatsapp::sendTemplate('+905551234567', 'your_template_name', 'tr');

    } else {
        logger()->error("WhatsApp API [{$e->getWhatsappErrorCode()}]: {$e->getWhatsappDescription()}");
    }
}
```

---

## Testing

```bash
composer install
vendor/bin/phpunit
```

In your own application tests, you can mock `WhatsappBot` to avoid real API calls:

```php
use OzkanOzcan\LaravelWhatsapp\WhatsappBot;
use OzkanOzcan\LaravelWhatsapp\WhatsappMessage;

$this->mock(WhatsappBot::class)
    ->shouldReceive('sendMessage')
    ->once()
    ->with('+905551234567', Mockery::type(WhatsappMessage::class))
    ->andReturn(['messages' => [['id' => 'wamid.test123']]]);

$user->notify(new OrderShipped($order));
```

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a history of changes.

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes following [Conventional Commits](https://www.conventionalcommits.org/)
4. Push and open a Pull Request against `development`

Please make sure all tests pass before submitting a PR.

---

## License

MIT © [Özkan Özcan — Özcan Teknoloji](https://ozcanyazilim.com.tr)  
See [LICENSE](LICENSE) for full details.
