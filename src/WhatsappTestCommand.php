<?php

namespace OzkanOzcan\LaravelWhatsapp;

use Illuminate\Console\Command;

class WhatsappTestCommand extends Command
{
    protected $signature = 'whatsapp:test
                            {--to= : Override the recipient phone number (E.164 format, e.g. +905551234567)}
                            {--message= : Custom test message text}';

    protected $description = 'Send a test message to verify your WhatsApp Business configuration';

    public function handle(WhatsappBot $bot): int
    {
        $this->info('Testing WhatsApp Business Cloud API connection...');
        $this->line('');

        // Resolve recipient
        $to = $this->option('to') ?: config('whatsapp.to');

        if (empty($to)) {
            $this->components->warn(
                'No recipient phone number provided. ' .
                'Set WHATSAPP_TO in your .env or pass --to option. ' .
                'Skipping message send.'
            );

            return self::SUCCESS;
        }

        $this->components->twoColumnDetail('Phone Number ID', config('whatsapp.phone_number_id') ?: '(not set)');
        $this->components->twoColumnDetail('Recipient', $to);
        $this->line('');

        // Send test message
        $msgText = $this->option('message')
            ?: "✅ *Laravel WhatsApp Notifier*\nYour WhatsApp Business integration is configured correctly and ready to send notifications.";

        try {
            $message = WhatsappMessage::create($msgText);
            $bot->sendMessage($to, $message);
            $this->components->info("Test message sent to [{$to}] successfully!");
        } catch (\Throwable $e) {
            $this->components->error('Failed to send test message: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
