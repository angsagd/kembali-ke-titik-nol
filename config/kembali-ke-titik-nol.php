<?php

return [
    'superadmin' => [
        'name' => env('KTN_SUPERADMIN_NAME', 'Superadmin KTN'),
        'email' => env('KTN_SUPERADMIN_EMAIL'),
        'whatsapp_number' => env('KTN_SUPERADMIN_WHATSAPP', '620000000001'),
        'password' => env('KTN_SUPERADMIN_PASSWORD', 'tgd0001'),
    ],

    'administrator' => [
        'name' => env('KTN_ADMINISTRATOR_NAME', 'Administrator KTN'),
        'email' => env('KTN_ADMINISTRATOR_EMAIL'),
        'whatsapp_number' => env('KTN_ADMINISTRATOR_WHATSAPP', '628100000002'),
        'password' => env('KTN_ADMINISTRATOR_PASSWORD', 'tgd0002'),
    ],

    'contacts_path' => env('KTN_CONTACTS_PATH', base_path('specification/contacts.json')),
];
