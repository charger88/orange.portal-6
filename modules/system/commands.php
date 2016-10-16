<?php

return [
    'text' => [
        'index' => [
            'name'   => 'Text',
            'action' => true,
            'block'  => true,
            'args'   => [
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
    ],
    'menu' => [
        'index' => [
            'name'   => 'Simple menu',
            'action' => true,
            'block'  => true,
            'args'   => [
                'root' => [
                    'name' => 'Menu root',
                    'type' => 'select',
                    'data' => '', // TODO Here will be URL for ajax request for data
                ],
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
        'tree' => [
            'name'   => 'Tree menu',
            'action' => true,
            'block'  => true,
            'args'   => [
                'root' => [
                    'name' => 'Menu root',
                    'type' => 'select',
                    'default' => 0,
                    'data' => '', // TODO Here will be URL for ajax request for data
                ],
                'levels' => [
                    'name' => 'Tree levels',
                    'type' => 'number',
                    'default' => 0,
                    'data' => '',
                ],
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
    ],
    'search' => [
        'index' => [
            'name'   => 'Search page with form and results',
            'action' => true,
            'block'  => false,
            'args'   => [
                'limit' => [
                    'name' => 'Number of results',
                    'type' => 'number',
                    'default' => 50,
                ],
            ],
        ],
        'form' => [
            'name'   => 'Search form',
            'action' => true,
            'block'  => true,
            'args'   => [],
        ],
    ],
    'signin' => [
        'index' => [
            'name'   => 'Signin form',
            'action' => true,
            'block'  => true,
            'args'   => [
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
    ],
    'system' => [
        'copyrights' => [
            'name'   => 'Copyrights',
            'action' => true,
            'block'  => true,
            'args'   => [
                'year_opened' => [
                    'name' => 'Year of site opening',
                    'type' => 'number',
                    'default' => date('Y'),
                ],
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
        'adminbar' => [
            'name'   => 'Admin bar',
            'action' => false,
            'block'  => true,
            'args'   => [
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
        'langswitcher' => [
            'name'   => 'Language switcher',
            'action' => false,
            'block'  => true,
            'args'   => [
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
    ],
    'tags' => [
        'cloud' => [
            'name'   => 'Tags cloud',
            'action' => true,
            'block'  => true,
            'args'   => [
                'limit' => [
                    'name' => 'Limit',
                    'type' => 'number',
                    'default' => 50,
                ],
                'prefix' => [
                    'name' => 'Prefix',
                    'type' => 'string',
                    'default' => 'default',
                ],
            ],
        ],
    ],
    'redirect' => [
        'index' => [
            'name'   => 'Redirect',
            'action' => true,
            'block'  => false,
            'args'   => [
                'url' => [
                    'name' => 'URL',
                    'type' => 'string',
                    'default' => OP_WWW,
                ],
                'prefix' => [
                    'name' => 'Permanent',
                    'type' => 'boolean',
                    'default' => '0',
                ],
            ],
        ],
    ],
];