<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'phpmailer' => [
        'host' => env('PHPMAILER_HOST', env('MAIL_HOST', 'smtp.mailgun.org')),
        'port' => env('PHPMAILER_PORT', env('MAIL_PORT', 587)),
        'username' => env('PHPMAILER_USERNAME', env('MAIL_USERNAME')),
        'password' => env('PHPMAILER_PASSWORD', env('MAIL_PASSWORD')),
        'encryption' => env('PHPMAILER_ENCRYPTION', env('MAIL_ENCRYPTION', 'tls')),
        'smtp_auth' => env('PHPMAILER_SMTP_AUTH', true),
        'from_address' => env('PHPMAILER_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', env('MAIL_USERNAME', 'hello@example.com'))),
        'from_name' => env('PHPMAILER_FROM_NAME', env('MAIL_FROM_NAME', env('APP_NAME', 'Smart Bus'))),
    ],

];
