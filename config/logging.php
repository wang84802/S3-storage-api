<?php

return [
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'slack'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('log/laravel.log'),
            'level' => 'debug'
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => ('https://hooks.slack.com/services/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'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],
    ],
];