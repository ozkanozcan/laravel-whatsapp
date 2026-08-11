<?php

return [
    'missing_token'          => 'WhatsApp erişim tokeni yapılandırılmamış. .env dosyanıza WHATSAPP_ACCESS_TOKEN ekleyin.',
    'missing_phone_id'       => 'WhatsApp Telefon Numarası ID yapılandırılmamış. .env dosyanıza WHATSAPP_PHONE_NUMBER_ID ekleyin.',
    'missing_recipient'      => 'WhatsApp alıcı telefon numarası belirtilmemiş.',
    'api_error'              => 'WhatsApp API Hatası [:code]: :description',
    'rate_limit'             => 'WhatsApp API hız limiti aşıldı. Lütfen daha fazla mesaj göndermeden önce bekleyin.',
    'invalid_token'          => 'WhatsApp erişim tokeni geçersiz veya süresi dolmuş. Meta Business Manager üzerinden yeni bir token oluşturun.',
    'invalid_phone_number'   => ':number numaralı alıcı geçerli bir WhatsApp numarası değil.',
    'outside_window'         => 'Mesaj gönderilemedi: 24 saatlik müşteri hizmet penceresi dolmuş. Bunun yerine bir şablon mesajı kullanın.',
    'max_retries_reached'    => 'WhatsApp API\'ye gönderim sırasında maksimum yeniden deneme sayısına ulaşıldı.',
];
