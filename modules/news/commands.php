<?php

return [
    'main' => [
        'index' => [
            'name'   => 'News digest',
            'action' => true,
            'block'  => true,
            'args'   => [
                'category' => [
                    'name' => 'Category',
                    'type' => 'number',
                    'default' => null,
                ],
                'digestlink' => [
                    'name' => 'Digest link',
                    'type' => 'digestlink',
                    'default' => '',
                ],
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
        'view' => [
            'name'   => 'News element',
            'action' => true,
            'block'  => false,
            'args'   => [
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
    ],
];