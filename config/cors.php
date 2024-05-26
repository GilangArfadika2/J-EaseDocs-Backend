<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => ['http://localhost:3000', 'https://j-easedocs-frontend-k7evvvd1r-gilangs-projects-60900864.vercel.app','https://j-easedocs-frontend-6hc1aha73-gilangs-projects-60900864.vercel.app/', 'https://j-easedocs-frontend.vercel.app'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],

    'allowed_headers' => ['Content-Type', 'Authorization'],

    'supports_credentials' => true,

];

