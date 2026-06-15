<?php

return [
    'enabled' => env('VISITOR_TRACKING_ENABLED', true),

    'capture_methods' => [
        'GET',
        'HEAD',
    ],

    'skip_paths' => [
        'api/*',
        'webhooks/*',
        'storage/*',
        'build/*',
        'css/*',
        'js/*',
        'images/*',
        'fonts/*',
        'favicon.ico',
        'up',
    ],

    'skip_extensions' => [
        'css',
        'js',
        'map',
        'png',
        'jpg',
        'jpeg',
        'gif',
        'svg',
        'webp',
        'ico',
        'woff',
        'woff2',
        'ttf',
        'eot',
        'otf',
        'mp4',
        'mp3',
        'wav',
        'webm',
        'pdf',
        'zip',
    ],

    'geo' => [
        'enabled' => env('VISITOR_GEO_ENABLED', true),
        'provider' => env('VISITOR_GEO_PROVIDER', 'ipapi'),
        'ipapi_url' => env('VISITOR_GEO_IPAPI_URL', 'http://ip-api.com/json/{ip}?fields=status,message,country,countryCode,regionName,city,lat,lon,timezone,query'),
        'ipinfo_token' => env('VISITOR_GEO_IPINFO_TOKEN', ''),
        'timeout' => env('VISITOR_GEO_TIMEOUT', 2),
        'cache_ttl' => env('VISITOR_GEO_CACHE_TTL', 604800),
    ],
];
