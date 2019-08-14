<?php

namespace NonexistentOffset;

class Foo
{

	public function nonexistentOffsetOnArray()
	{
		$array = [
			'a' => new \stdClass(),
			2,
		];

		echo $array['a'];
		echo $array[0];
		echo $array['b'];
		echo $array[1];
	}

	public function assigningNewKeyToLiteralArray()
	{
		$array = [];
		$array[] = 0;
		$array['aaa'] = 1;

		/** @var string $key */
		$key = doFoo();
		$array[$key] = 2;
	}

	public function assigningToNullable()
	{
		$null = null;
		$null[] = 'test';

		/** @var mixed[]|null $nullable */
		$nullable = doFoo();
		$nullable['test'] = 0;
		echo $nullable['testt'];
	}

	public function unsetOffset()
	{
		$array = [
			'a' => new \stdClass(),
			'b' => 1,
		];

		echo $array['a'];
		echo $array['b'];

		unset($array['a']);

		echo $array['a'];
		echo $array['b'];
	}

	public function arrayAfterForeaches()
	{
		$result = [
			'id' => 'blabla', // string
			'allowedRoomCounter' => 0,
			'roomCounter' => 0,
		];

		foreach ([1, 2] as $x) {
			$result['allowedRoomCounter'] += $x;
		}

		foreach ([3, 4] as $x) {
			$result['roomCounter'] += $x;
		}
	}

	public function errorType()
	{
		$array = [
			'foo' => NONEXISTENT_CONSTANT,
		];
		echo $array['foo'];
	}

	public function cumulative()
	{
		$arr = [1, 1, 1, 1, 2, 5, 3, 2];
		/** @var (string|int)[] */
		$cumulative = [];

		foreach ($arr as $val) {
			if (!isset($cumulative[$val])) {
				$cumulative[$val] = 0;
			}

			$cumulative[$val] = $cumulative[$val] + 1;
		}
	}

	public function classDoesNotExist(Bar $foo)
	{
		echo $foo['bar'];
		$foo[] = 'test';
	}

	/**
	 * @param array<string, string> $array
	 * @param int $i
	 */
	public function trickyArrayCasting(array $array, int $i)
	{
		echo $array[0];
		echo $array['0'];
		echo $array['foo'];
		echo $array[$i];
	}

	public function assigningToNull()
	{
		$null = null;
		$null['test'] = 'foo';
	}

	public function readingNull()
	{
		$null = null;
		echo $null['test'];
	}

	/**
	 * @param int $int
	 * @param float $float
	 * @param bool $bool
	 * @param resource $resource
	 */
	public function offsetAccessibleOnPrimitiveTypes(
		int $int,
		float $float,
		bool $bool,
		$resource
	)
	{
		$int[42];
		$float[42];
		$bool[42];
		$resource[42];
	}

	public function offsetExistsOnArrayAccess(
		\ArrayAccess $access
	)
	{
		echo $access['name'];
	}

	public function issetProblem(string $s)
	{
		$a = [
			'b' => ['c' => false],
			'c' => ['c' => true],
			'd' => ['e' => true]
		];
		if (isset($a[$s]['c'])) {
			echo $a[$s];
			echo $a[$s]['c'];
		}
		if (isset($a['b']['c'])) {
			echo $a['b'];
			echo $a['b']['c'];
		}

		echo $a[$s]['c'];
	}

	public function issetProblem2(float $amount, int $bar)
	{
		if ($amount > 0) {
			$map = [
				1 => 1,
				2 => 2,
			];
		} elseif ($amount < 0) {
			$map = [
				3 => 3,
				4 => 4,
			];
		} else {
			$map = [];
		}

		echo $map[$bar];

		if (!isset($map[$bar])) {
			echo $map[$bar];
			throw new \Exception();
		}

		return $map[$bar];
	}

	private $propertyThatWillBeSetToArray;

	public function assignmentToProperty()
	{
		$this->propertyThatWillBeSetToArray = [];
		$this->propertyThatWillBeSetToArray['foo'] = 1;
		echo $this->propertyThatWillBeSetToArray['foo'];
	}

	public function offsetAccessArrayMaybe(array $strings)
	{
		echo $strings[0];

		if (isset($strings['foo'])) {
			echo $strings['bar'];
		}
	}

	public function constantStringStillUndefinedInGeneralStringIsset(string $s)
	{
		$a = [
			'a' => 'blabla',
		];

		echo $a[$s];
		echo $a['b'];
		if (isset($a[$s])) {
			echo $a[$s];
			echo $a['b'];
		}
	}

	/**
	 * @param array<int, mixed> $array
	 */
	public function generalArrayHasOffsetOfDifferentType(
		array $array,
		string $s
	)
	{
		echo $array[$s];
		if (isset($array[$s])) {
			echo $array[$s];
		}
	}

	public function issetEliminatesOffsetInaccessibleType()
	{
		$a = ['a' => 1, 'b' => 1];
		if (rand(0, 1) === 1) {
			$a = function () {

			};
			if (isset($a['a'])) {

			}
		}

		if (isset($a['a'])) {
			echo $a['a'];
			echo $a['b'];
		}
	}

	public function accessOnString(string $s)
	{
		echo $s[1];
	}

	/**
	 * @param self[] $array
	 * @param array<int, string> $intKeys
	 */
	public function benevolentUnionType(array $array, array $intKeys)
	{
		foreach ($array as $key => $foo) {
			echo $intKeys[$key];
		}
	}

	public function castToArrayKeyType()
	{
		$array = [
			'1' => [
				'foo' => 'bar',
			],
		];
		return $array['1'];
	}

	/**
	 * @param array<int, string> $intArray
	 * @param array<string, string> $stringArray
	 * @param array<int|string, string> $intOrStringArray
	 * @param int $int
	 * @param string $string
	 * @param int|null $intOrNull
	 */
	public function arraysWithNull(
		array $intArray,
		array $stringArray,
		array $intOrStringArray,
		int $int,
		string $string,
		$intOrNull
	)
	{
		echo $intArray[$int];
		echo $intArray[$string];
		echo $intArray[$intOrNull];
		echo $intArray[null];

		echo $stringArray[$int];
		echo $stringArray[$string];
		echo $stringArray[$intOrNull];
		echo $stringArray[null];

		echo $intOrStringArray[$int];
		echo $intOrStringArray[$string];
		echo $intOrStringArray[$intOrNull];
		echo $intOrStringArray[null];
	}

	public function simpleXMLElementArrayAccess(\SimpleXMLElement $xml)
	{
		echo $xml['asdf'];
	}

	public function simpleXMLElementSubclassArrayAccess(SubClassSimpleXMLElement $xml)
	{
		echo $xml['asdf'];
	}
}

class SubClassSimpleXMLElement extends \SimpleXMLElement
{
}

class OffsetAfterForLoop
{

	public function doFoo(int $x)
	{
		$tags = [];
		for ($i = 0; $i < 10; $i ++) {
			$tags[$i] = $x;
		}

		$tags[1] === $tags[1];
	}

}

class Coalesce
{

	public function doFoo()
	{
		$a = [];
		echo $a['foo'] ?? 'foo';
	}

}
