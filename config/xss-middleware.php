<?php

return [
    /*
     |--------------------------------------------------------------------------
     | enable auto register middleware for all requests
     |--------------------------------------------------------------------------
     |
     | if you want auto register for custom middleware group
     |      'auto_register_middleware' => ['web'], // for web group only
     |      'auto_register_middleware' => true, //  all groups
     |      'auto_register_middleware' => false, // none
     */
    'auto_register_middleware' => true,

    /*
     * The middleware will used to filter xss
     */
    'middleware'               => \Alkhwlani\XssMiddleware\XSSFilterMiddleware::class,

    'except' => []
];
