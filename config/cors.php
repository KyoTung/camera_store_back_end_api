<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'upload', 'temp-images'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://camera-store-frontend-eta.vercel.app'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,

];
