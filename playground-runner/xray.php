<?php declare(strict_types = 1);

/**
 * Turns the collected AST + expression types into the compact "AST X-Ray"
 * payload the playground editor renders:
 *
 *   strings: every kind, type, property name and rendered scalar, interned
 *   nodes:   [kind, from, to, parent, type, props] in pre-order, where kind and
 *            type index into strings (type -1 when PHPStan has none), and props
 *            is a flat [name, value, name, value, ...] list: name indexes into
 *            strings, value is a child node index (>= 0), a list of those, or
 *            -(stringIndex + 1) for a rendered scalar.
 *
 * Offsets are converted from php-parser's byte offsets to UTF-16 code units,
 * which is what CodeMirror (JavaScript strings) counts in.
 */

use PHPStanPlayground\XRay\AstNodesCollector;
use PHPStanPlayground\XRay\ExprTypesCollector;

/**
 * @param array<string, list<mixed>> $fileCollectedData collected data of the analysed file, keyed by collector class
 * @return array{strings: list<string>, nodes: list<array{int, int, int, int, int, list<int|list<int>>}>}
 */
function playground_xray(string $code, array $fileCollectedData): array
{
	$byteToUtf16 = playground_xray_offset_map($code);
	$offset = static fn (int $byte): int => $byteToUtf16 === null ? $byte : ($byteToUtf16[$byte] ?? $byteToUtf16[strlen($code)]);

	/** @var array<string, string> $typesByRange */
	$typesByRange = [];
	foreach ($fileCollectedData[ExprTypesCollector::class] ?? [] as $data) {
		/** @var array{int, int, string, string} $data */
		$typesByRange[$data[2] . '@' . $data[0] . ':' . $data[1]] = $data[3];
	}

	/** @var array<string, int> $stringIndex */
	$stringIndex = [];
	/** @var list<string> $strings */
	$strings = [];
	$intern = static function (string $string) use (&$stringIndex, &$strings): int {
		if (!isset($stringIndex[$string])) {
			$strings[] = $string;
			$stringIndex[$string] = count($strings) - 1;
		}

		return $stringIndex[$string];
	};
	$value = static fn (int|string $value): int => is_int($value) ? $value : -($intern($value) + 1);

	$nodes = [];
	foreach ($fileCollectedData[AstNodesCollector::class] ?? [] as $astNodes) {
		/** @var list<array{string, int, int, int, list<array{string, int|string|list<int|string>}>}> $astNodes */
		foreach ($astNodes as [$kind, $start, $end, $parent, $props]) {
			$type = $typesByRange[$kind . '@' . $start . ':' . $end] ?? null;
			$flatProps = [];
			foreach ($props as [$name, $propValue]) {
				$flatProps[] = $intern($name);
				$flatProps[] = is_array($propValue) ? array_map($value, $propValue) : $value($propValue);
			}
			$nodes[] = [
				$intern($kind),
				$offset($start),
				$offset($end),
				$parent,
				$type === null ? -1 : $intern($type),
				$flatProps,
			];
		}

		// One FileNode per file, so one collected entry.
		break;
	}

	return [
		'strings' => $strings,
		'nodes' => $nodes,
	];
}

/**
 * Byte offset -> UTF-16 code unit offset, or null when the code is ASCII and
 * the two coincide.
 *
 * @return array<int, int>|null
 */
function playground_xray_offset_map(string $code): ?array
{
	if (preg_match('/[\x80-\xFF]/', $code) !== 1) {
		return null;
	}

	$map = [];
	$units = 0;
	$length = strlen($code);
	$byte = 0;
	while ($byte < $length) {
		$first = ord($code[$byte]);
		if ($first < 0x80) {
			$charBytes = 1;
			$charUnits = 1;
		} elseif ($first < 0xE0) {
			$charBytes = 2;
			$charUnits = 1;
		} elseif ($first < 0xF0) {
			$charBytes = 3;
			$charUnits = 1;
		} else {
			$charBytes = 4;
			$charUnits = 2; // astral plane: a surrogate pair in UTF-16
		}
		for ($i = 0; $i < $charBytes; $i++) {
			$map[$byte + $i] = $units;
		}
		$byte += $charBytes;
		$units += $charUnits;
	}
	$map[$length] = $units;

	return $map;
}
