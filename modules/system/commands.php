<?php

return [
	'text' => [
		'index' => [
			'name' => 'Text',
			'action' => true,
			'block' => true,
			'args' => [
				'prefix' => [
					'name' => 'Prefix',
					'type' => 'string',
					'default' => 'default',
				],
			],
		],
		'data' => [
			'name' => 'Data',
			'action' => true,
			'block' => true,
			'args' => [],
		],
	],
	'menu' => [
		'index' => [
			'name' => 'Simple menu',
			'action' => true,
			'block' => true,
			'args' => [
				'root' => [
					'name' => 'Menu root',
					'type' => 'select',
					'data' => OP_WWW . '/admin/pages/select',
				],
				'prefix' => [
					'name' => 'Prefix',
					'type' => 'string',
					'default' => 'default',
				],
			],
		],
		'tree' => [
			'name' => 'Tree menu',
			'action' => true,
			'block' => true,
			'args' => [
				'root' => [
					'name' => 'Menu root',
					'type' => 'select',
					'default' => 0,
					'data' => OP_WWW . '/admin/pages/select',
				],
				'levels' => [
					'name' => 'Tree levels',
					'type' => 'number',
					'default' => 0,
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
			'name' => 'Search page with form and results',
			'action' => true,
			'block' => false,
			'args' => [
				'limit' => [
					'name' => 'Number of results',
					'type' => 'number',
					'default' => 50,
				],
			],
		],
		'form' => [
			'name' => 'Search form',
			'action' => true,
			'block' => true,
			'args' => [],
		],
	],
	'signin' => [
		'index' => [
			'name' => 'Signin form',
			'action' => true,
			'block' => true,
			'args' => [
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
			'name' => 'Copyrights',
			'action' => true,
			'block' => true,
			'args' => [
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
			'name' => 'Admin bar',
			'action' => false,
			'block' => true,
			'args' => [
				'prefix' => [
					'name' => 'Prefix',
					'type' => 'string',
					'default' => 'default',
				],
			],
		],
		'langswitcher' => [
			'name' => 'Language switcher',
			'action' => false,
			'block' => true,
			'args' => [
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
			'name' => 'Tags cloud',
			'action' => true,
			'block' => true,
			'args' => [
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
			'name' => 'Redirect',
			'action' => true,
			'block' => false,
			'args' => [
				'url' => [
					'name' => 'URL',
					'type' => 'string',
					'default' => OP_WWW,
				],
				'prefix' => [
					'name' => 'Permanent',
					'type' => 'select',
					'default' => '0',
					'data' => [0 => 'No', 1 => 'Yes'],
				],
			],
		],
	],
];