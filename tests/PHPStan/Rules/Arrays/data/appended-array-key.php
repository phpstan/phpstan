<?php

namespace AppendedArrayKey;

class Foo
{

	/**
	 * @param array<int, mixed> $intArray
	 * @param array<string, mixed> $stringArray
	 * @param array<int|string, mixed> $bothArray
	 * @param int|string $intOrString
	 */
	public function doFoo(
		array $intArray,
		array $stringArray,
		array $bothArray,
		int $int,
		string $string,
		$intOrString,
		?string $stringOrNull
	)
	{
		function () use ($intArray) {
			$intArray[new \DateTimeImmutable()] = 1;
		};
		function () use ($intArray, $intOrString) {
			$intArray[$intOrString] = 1;
		};
		function () use ($intArray, $int) {
			$intArray[$int] = 1;
		};
		function () use ($intArray, $string) {
			$intArray[$string] = 1;
		};
		function () use ($stringArray, $int) {
			$stringArray[$int] = 1;
		};
		function () use ($stringArray, $string) {
			$stringArray[$string] = 1;
		};
		function () use ($stringArray, $intOrString) {
			$stringArray[$intOrString] = 1;
		};
		function () use ($bothArray, $int) {
			$bothArray[$int] = 1;
		};
		function () use ($bothArray, $intOrString) {
			$bothArray[$intOrString] = 1;
		};
		function () use ($bothArray, $string) {
			$bothArray[$string] = 1;
		};
		function () use ($stringArray, $stringOrNull) {
			$stringArray[$stringOrNull] = 1; // will be cast to string
		};
		function () use ($stringArray) {
			$stringArray['0'] = 1;
		};
	}

}
