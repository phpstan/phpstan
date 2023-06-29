<?php declare(strict_types = 1);

namespace App;

use Nette\DI\Helpers;
use Nette\Neon\Neon;
use function header;
use function json_encode;
use function preg_quote;

require __DIR__ . '/vendor/autoload.php';

$message = $_GET['message'] ?? '';
$path = $_GET['path'] ?? null;

header('Content-Type: application/json; charset=utf-8');

if (!$path) {
	$data = [
		'parameters' => [
			'ignoreErrors' => [
				Helpers::escape('#^' . preg_quote($message, '#') . '$#'),
			],
		],
	];

	echo json_encode(['neon' => Neon::encode($data, true)]);
	return;
}

$data = [
	'parameters' => [
		'ignoreErrors' => [
			[
				'message' => Helpers::escape('#^' . preg_quote($message, '#') . '$#'),
				'path' => Helpers::escape($path),
			]
		],
	],
];

echo json_encode(['neon' => Neon::encode($data, true)]);
