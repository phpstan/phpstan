<?php declare(strict_types = 1);

/**
 * Runs the real bref.php handler on a few requests before a deploy.
 *
 * Run it through smoke-test.sh, not directly: the handler wipes /tmp on every request,
 * so it has to run inside a throwaway container.
 *
 * The requests share one process, as they do on Lambda with BREF_LOOP_MAX, and switch
 * PHP versions back and forth - so a request answered with the previous request's
 * global state fails here too.
 */

$handler = require __DIR__ . '/bref.php';

$returnTypeCode = <<<'PHP'
<?php declare(strict_types = 1);

function foo(int $i): string
{
	return $i;
}
PHP;

$strContainsCode = <<<'PHP'
<?php declare(strict_types = 1);

var_dump(str_contains('abc', 'b'));
PHP;

$requests = [
	'return type, PHP 8.5' => [
		['code' => $returnTypeCode, 'level' => '8', 'phpVersion' => 80500],
		['Function foo() should return string but returns int.'],
	],
	'str_contains, PHP 7.4' => [
		['code' => $strContainsCode, 'level' => '8', 'phpVersion' => 70400],
		['Function str_contains not found.'],
	],
	'str_contains, PHP 8.5' => [
		['code' => $strContainsCode, 'level' => '8', 'phpVersion' => 80500],
		[],
	],
	'strict rules + bleeding edge, PHP 8.5' => [
		['code' => $returnTypeCode, 'level' => '9', 'phpVersion' => 80500, 'strictRules' => true, 'bleedingEdge' => true],
		['Function foo() should return string but returns int.'],
	],
	'str_contains, PHP 7.4 again' => [
		['code' => $strContainsCode, 'level' => '8', 'phpVersion' => 70400],
		['Function str_contains not found.'],
	],
];

$failed = false;
foreach ($requests as $name => [$event, $expectedMessages]) {
	try {
		$response = $handler($event);
	} catch (Throwable $e) {
		echo sprintf("FAIL %s: %s: %s\n%s\n", $name, get_class($e), $e->getMessage(), $e->getTraceAsString());
		$failed = true;
		continue;
	}

	$messages = array_column($response['result'], 'message');
	if ($messages !== $expectedMessages) {
		echo sprintf("FAIL %s:\n  expected: %s\n  actual:   %s\n", $name, json_encode($expectedMessages), json_encode($messages));
		$failed = true;
		continue;
	}

	echo sprintf("OK   %s (PHPStan %s)\n", $name, $response['version']);
}

exit($failed ? 1 : 0);
