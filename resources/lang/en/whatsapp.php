<?php

return [
    'missing_token'          => 'WhatsApp access token is not configured. Set WHATSAPP_ACCESS_TOKEN in your .env file.',
    'missing_phone_id'       => 'WhatsApp Phone Number ID is not configured. Set WHATSAPP_PHONE_NUMBER_ID in your .env file.',
    'missing_recipient'      => 'No WhatsApp recipient phone number provided.',
    'api_error'              => 'WhatsApp API Error [:code]: :description',
    'rate_limit'             => 'WhatsApp API rate limit reached. Please wait before sending more messages.',
    'invalid_token'          => 'The WhatsApp access token is invalid or has expired. Generate a new token from Meta Business Manager.',
    'invalid_phone_number'   => 'The recipient phone number :number is not a valid WhatsApp number.',
    'outside_window'         => 'Message cannot be sent: the 24-hour customer service window has expired. Use a template message instead.',
    'max_retries_reached'    => 'Max retry attempts reached when sending to WhatsApp API.',
];
