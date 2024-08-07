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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    // adding genesys configuration
    /*
            $this->clientId = config('services.genesys.client_id');
        $this->clientSecret = config('services.genesys.client_secret');
        $this->baseUrl = config('services.genesys.base_url');
        */
    'genesys' => [
        'client_id' => env('GENESYS_CLIENT_ID'),
        'client_secret' => env('GENESYS_CLIENT_SECRET'),
        'base_url' => env('GENESYS_BASE_URL'),
    ],
    'e911' => [
        'base_url' => env('E911_API_BASE_URL', 'https://api.e911cloud.com'),
        'username' => env('E911_API_USERNAME'),
        'password' => env('E911_API_PASSWORD'),
    ],

];
