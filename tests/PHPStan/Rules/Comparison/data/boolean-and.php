<?php

namespace ConstantCondition;

class BooleanAnd
{

	public function doFoo(int $i, bool $j, \stdClass $std, ?\stdClass $nullableStd)
	{
		if ($i && $j) {

		}

		$one = 1;
		if ($one && $i) {

		}

		if ($i && $std) {

		}

		$zero = 0;
		if ($zero && $i) {

		}
		if ($i && $zero) {

		}
		if ($one === 0 && $one) {

		}
		if ($one === 1 && $one) {

		}
		if ($nullableStd && $nullableStd) {

		}
		if ($nullableStd !== null && $nullableStd) {

		}
	}

	/**
	 * @param Foo|Bar $union
	 * @param Lorem&Ipsum $intersection
	 */
	public function checkUnionAndIntersection($union, $intersection)
	{
		if ($union instanceof Foo && $union instanceof Bar) {

		}

		if ($intersection instanceof Lorem && $intersection instanceof Ipsum) {

		}

		if ($union instanceof Foo || $union instanceof Bar) {

		} elseif ($union instanceof Foo && doFoo()) {

		}

		if ($intersection instanceof Lorem && $intersection instanceof Ipsum) {

		} elseif ($intersection instanceof Lorem && doFoo()) {

		}
	}

}

class NonNullablePropertiesShouldNotReportError
{

	/** @var self */
	private $foo;

	/** @var self */
	private $bar;

	public function doFoo()
	{
		if ($this->foo !== null && $this->bar !== null) {

		}
	}

}

class StringInIsset
{

	public function doFoo(string $s, string $t)
	{
		if (isset($s[1]) && isset($t[1])) {

		}
	}

}

class IssetBug
{

	public function doFoo(string $alias, array $options = [])
	{
		list($name, $p) = explode('.', $alias);
		if (isset($options['c']) && !\strpos($options['c'], '\\')) {
			// ...
		}

		if (!isset($options['c']) && \strpos($p, 'X') === 0) {
			// ?
		}
	}

}

class IntegerRangeType
{

	public function doFoo(int $i)
	{
		if ($i < 3 && $i > 5) { // can never happen
		}
	}

}
