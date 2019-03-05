<?php

namespace PHPStan\Type\Php\MinMaxData;

use PHPStan\Testing\AnalysisBased\Utils;

Utils::assertTypeDescription(min([1, 2, 3]), '1');
Utils::assertTypeDescription(min([1, 2, 3], [4, 5, 5]), 'array(1, 2, 3)');
Utils::assertTypeDescription(min(...[1, 2, 3]), '1');
Utils::assertTypeDescription(min(...[2, 3, 4], ...[5, 1, 8]), '1');
Utils::assertTypeDescription(min(0, ...[1, 2, 3]), '0');

Utils::assertTypeDescription(max([1, 10, 8], [5, 6, 9]), 'array(5, 6, 9)');
Utils::assertTypeDescription(max(array(2, 2, 2), array(1, 1, 1, 1)), 'array(1, 1, 1, 1)');

/** @var int[] $arrayOfUnknownIntegers */
$arrayOfUnknownIntegers = doFoo();
Utils::assertTypeDescription(max($arrayOfUnknownIntegers, $arrayOfUnknownIntegers), 'array<int>');

Utils::assertTypeDescription(min(...[1.1, 2.2, 3.3]), '1.1');
Utils::assertTypeDescription(min(...[1.1, 2, 3]), '1.1');

Utils::assertTypeDescription(max(...[1, 2, 3]), '3');
Utils::assertTypeDescription(max(...[1.1, 2.2, 3.3]), '3.3');

Utils::assertTypeDescription(min(1, 2, 3), '1');
Utils::assertTypeDescription(max(1, 2, 3), '3');
Utils::assertTypeDescription(min(1.1, 2.2, 3.3), '1.1');
Utils::assertTypeDescription(max(1.1, 2.2, 3.3), '3.3');
Utils::assertTypeDescription(min(1, 1), '1');

Utils::assertTypeDescription(min(1), '*ERROR*');

/** @var int $integer */
$integer = doFoo();
/** @var string $string */
$string = doFoo();
Utils::assertTypeDescription(min($integer, $string), 'int|string');
Utils::assertTypeDescription(min([$integer, $string]), 'int|string');
Utils::assertTypeDescription(min(...[$integer, $string]), 'int|string');
Utils::assertTypeDescription(min('a', 'b'), '\'a\'');
Utils::assertTypeDescription(max(new \DateTimeImmutable("today"), new \DateTimeImmutable("tomorrow")), 'DateTimeImmutable');
Utils::assertTypeDescription(min(1, 2.2, 3.3), '1');
