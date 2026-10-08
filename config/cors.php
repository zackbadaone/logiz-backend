<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | هنا نقوم بضبط السياسات الأمنية للسيرفر لفتح قنوات العبور الحية.
    | تم تحصينها لتسمح للواجهة السحابية المعتمدة والمحلية فقط بالاتصال الآمن.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // السماح بالروابط المحلية للسفيرفر والفرونت أند لمنع قفل الاتصال أثناء التطوير
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'), 
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://localhost:8000',
        'http://127.0.0.1:8000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // تفعيل دعم الصلاحيات لاستقبال التوكنز والجلسات بأمان
    'supports_credentials' => true,

];