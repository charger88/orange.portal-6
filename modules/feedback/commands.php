<?php

return [
	'main' => [
		'index' => [
			'name' => 'Feedback form',
			'action' => true,
			'block' => true,
			'args' => [
				'form' => [
					'name' => 'Form ID',
					'type' => 'number',
					'default' => 1,
				],
			],
		],
	],
];