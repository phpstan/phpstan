<?php declare(strict_types=1);

namespace CallToArrayFunction;

/**
 * @param array<string, string> $array
 */
function assoc(array $array)
{
}

/**
 * @param array<int, string> $array
 */
function numeric(array $array)
{
}

function test()
{
	$array = ['1' => ''];
	assoc($array);
	numeric($array);

	$intKeys = [1 => ''];
	assoc($intKeys);
	numeric($intKeys);

	$strKeys = ['a' => ''];
	assoc($strKeys);
	numeric($strKeys);
}
