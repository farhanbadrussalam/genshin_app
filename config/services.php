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

    /*
    |--------------------------------------------------------------------------
    | HoYoLAB Python Microservice
    |--------------------------------------------------------------------------
    | Konfigurasi untuk berkomunikasi dengan FastAPI microservice (genshin.py).
    | Set HOYOLAB_MICROSERVICE_URL di .env sesuai environment Anda:
    |   - Local Docker (WSL): http://localhost:8001
    |   - Dalam Docker network: http://hoyolab-service:8001
    */
    'hoyolab_microservice' => [
        'url'     => env('HOYOLAB_MICROSERVICE_URL', 'http://localhost:8001'),
        'timeout' => env('HOYOLAB_MICROSERVICE_TIMEOUT', 30),
    ],

];
