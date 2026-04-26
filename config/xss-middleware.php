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
    'middleware' => \Alkhwlani\XssMiddleware\XSSFilterMiddleware::class,

    /*
     * Field names that should be skipped entirely (no sanitisation, no
     * invisible-character stripping). Useful for fields that intentionally
     * carry HTML/markdown payloads, e.g. rich-text editor content or webhook
     * bodies.
     */
    'except' => [],

    /*
     |--------------------------------------------------------------------------
     | Evil options
     |--------------------------------------------------------------------------
     |
     | Custom evil patterns to strip on top of voku/anti-xss's built-in list.
     | All sub-keys are optional; set to null or omit to skip.
     |
     |     'evil' => [
     |         'attributes'         => ['style', 'srcdoc'],   // addEvilAttributes
     |         'tags'               => ['svg', 'math'],        // addEvilHtmlTags
     |         'regex'              => ['/foo[0-9]+/'],        // addNeverAllowedRegex
     |         'events'             => ['onmycustom'],         // addNeverAllowedOnEventsAfterwards
     |         'strAfterwards'      => ['SECRET'],             // addNeverAllowedStrAfterwards
     |         'doNotCloseHtmlTags' => ['br', 'hr'],           // addDoNotCloseHtmlTags
     |     ],
     */
    'evil' => [
        'attributes' => null,
        'tags' => null,
    ],

    /*
     |--------------------------------------------------------------------------
     | Replacement string
     |--------------------------------------------------------------------------
     |
     | The string used to replace removed portions of input where XSS was
     | detected. Null preserves voku's default (empty string).
     */
    'replacement' => null,
];
