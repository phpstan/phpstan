<?php

namespace CheckTypeFunctionCall;

class Foo
{

	/**
	 * @param int $integer
	 * @param int|string $integerOrString
	 * @param string $string
	 * @param callable $callable
	 * @param array $array
	 * @param array<int> $arrayOfInt
	 */
	public function doFoo(
		int $integer,
		$integerOrString,
		string $string,
		callable $callable,
		array $array,
		array $arrayOfInt
	)
	{
		if (is_int($integer)) { // always true

		}
		if (is_int($integerOrString)) { // fine

		}
		if (is_int($string)) { // always false

		}
		$className = 'Foo';
		if (is_a($className, \Throwable::class, true)) { // should be fine

		}
		if (is_array($callable)) {

		}
		if (is_callable($array)) {

		}
		if (is_callable($arrayOfInt)) {

		}

		assert($integer instanceof \stdClass);
	}

}

class TypeCheckInSwitch
{

	public function doFoo($value)
	{
		switch (true) {
			case is_int($value):
			case is_float($value):
				break;
		}
	}

}

class StringIsNotAlwaysCallable
{

	public function doFoo(string $s)
	{
		if (is_callable($s)) {
			$s();
		}
	}

}

class CheckIsCallable
{

	public function test()
	{
		if (is_callable('date')) {

		}
		if (is_callable('nonexistentFunction')) {

		}
	}

}

class IsNumeric
{

	public function test(string $str, float $float)
	{
		if (is_numeric($str)) {

		}
		if (is_numeric('123')) {

		}
		if (is_numeric('blabla')) {

		}

		$isNumeric = $float;
		$maybeNumeric = $float;
		if (doFoo()) {
			$isNumeric = 123;
			$maybeNumeric = 123;
		} else {
			$maybeNumeric = $str;
		}

		if (is_numeric($isNumeric)) {

		}
		if ($maybeNumeric) {

		}
	}

}

class CheckDefaultArrayKeys
{

	/**
	 * @param string[] $array
	 */
	public function doFoo(array $array)
	{
		foreach ($array as $key => $val) {
			if (is_int($key)) {
				return;
			}
			if (is_string($key)) {
				return;
			}
		}
	}

}

class IsSubclassOfTest
{

	public function doFoo(
		string $string,
		?string $nullableString
	)
	{
		is_subclass_of($string, $nullableString);
		is_subclass_of($nullableString, $string);
		is_subclass_of($nullableString, 'Foo');
	}

}

class DefinedConstant
{

	public function doFoo()
	{
		if (defined('DEFINITELY_DOES_NOT_EXIST')) {

		}
		if (!defined('ANOTHER_DEFINITELY_DOES_NOT_EXIST')) {

		}

		$foo = new Foo();
		if (method_exists($foo, 'test')) {

		}
		if (method_exists($foo, 'doFoo')) {

		}
	}

}

final class FinalClassWithMethodExists
{

	public function doFoo()
	{
		if (method_exists($this, 'doFoo')) {

		}
		if (method_exists($this, 'doBar')) {

		}
	}

}

final class FinalClassWithPropertyExists
{

	/** @var int */
	private $fooProperty;

	public function doFoo()
	{
		if (property_exists($this, 'fooProperty')) {

		}
		if (property_exists($this, 'barProperty')) {

		}
	}

}

class InArray
{

	public function doFoo(
		string $s,
		int $i
	)
	{
		if (in_array($s, ['foo' ,'bar'], true)) {

		}
		if (in_array($i, ['foo', 'bar'], true)) {

		}

		$fooOrBar = 'foo';
		if (rand(0, 1) === 0) {
			$fooOrBar = 'bar';
		}

		if (in_array($fooOrBar, ['baz', 'lorem'], true)) {

		}

		if (in_array($fooOrBar, ['foo', 'bar'], true)) {

		}

		if (in_array('foo', ['foo'], true)) {

		}
	}

	/**
	 * @param string $s
	 * @param string[] $strings
	 */
	public function doBar(
		string $s,
		array $strings
	)
	{
		if (in_array($s, $strings, true)) {

		}
	}

	/**
	 * @param string $s
	 * @param array $mixedArray
	 * @param (string|float)[] $stringsOrFloats
	 */
	public function doBaz(
		string $s,
		array $mixedArray,
		array $stringsOrFloats
	)
	{
		if (in_array($s, $mixedArray, true)) {

		}
		if (in_array('s', $mixedArray, true)) {

		}
		if (in_array($s, $stringsOrFloats, true)) {

		}
		if (in_array('s', $stringsOrFloats, true)) {

		}
	}

}
