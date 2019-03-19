<?php

namespace ConstantCondition;

class Foo
{

}

class Bar
{

}

interface Lorem
{

}

interface Ipsum
{

}

class IfCondition
{

	/**
	 * @param int $i
	 * @param \stdClass $std
	 * @param Foo|Bar $union
	 * @param Lorem&Ipsum $intersection
	 */
	public function doFoo(int $i, \stdClass $std, $union, $intersection)
	{
		if ($i) {

		}

		if ($std) {

		}

		$zero = 0;
		if ($zero) {

		}

		if ($union instanceof Foo || $union instanceof Bar) {

		}

		if ($union instanceof Foo && $union instanceof Bar) {

		}

		if ($intersection instanceof Lorem && $intersection instanceof Ipsum) {

		}

		if ($intersection instanceof Lorem || $intersection instanceof Ipsum) {

		}
	}

	public function conditionalArray()
	{
		$arr = [];

		if (doFoo()) {
			$arr += ['abc'];
		}

		if ($arr) {

		}
	}

	public function skipDifferentRule()
	{
		if (!false) {

		}
		if (!true) {

		}
	}

	public function skipTypeSpecifyingFunctions(
		object $object
	)
	{
		if (is_object($object)) {

		}
		if (always_true()) {

		}
	}

}

final class FinalClass
{

	const FOO = true;

	public function doFoo()
	{
		if (self::FOO) {

		}
		if (static::FOO) {

		}
	}

}

class NotFinalClass
{

	const FOO = true;

	public function doFoo()
	{
		if (self::FOO) {

		}
		if (static::FOO) {

		}
	}

}

class IgnoredBreakBranch
{

	public function doFoo()
	{
		$hasBar = false;
		foreach (['a','b'] as $key) {
			if (rand(0,100) > 50) {
				if (rand(0,100) > 50) {
					$hasBar = true;
					break;
				}
				return 'foo';
			}
		}

		if ($hasBar) {
			return 'bar';
		}
		return 'default';
	}

	public function doBar()
	{
		$a = false;

		foreach ([1, 2, 3] as $_) {
			if (rand(0, 1)) {
				break;
			}
			$a = true;
		}

		if ($a) {}
	}

	public function doBaz(array $arr)
	{
		$a = false;

		foreach ($arr as $_) {
			if (rand(0, 1)) {
				break;
			}
			$a = true;
		}

		if ($a) {}
	}

}

class ClosureWithReturn
{

	public function doFoo(self $foo)
	{
		$f = function ($cond) use (&$var) {
			if ($cond) {
				$var = true;
				return;
			}
			$var = false;
		};

		$foo->doFoo($foo);

		if ($var) {

		}
	}

}

class ForeachWithContinue
{

	public function doFoo()
	{
		$tokens = token_get_all('<?php echo "hello";');

		$potential = false;
		foreach ($tokens as $token) {

			if (T_VARIABLE === $token[0]) {
				$potential = true;
				continue;
			}

			if ($potential) {
			}

			return false;
		}
	}

}

class BreaklessSwitch
{

	public function doFoo(string $country)
	{
		switch ($country) {
			case 'us':
				$country = '';
			// Intentionally no break
			default:
				if ($country) {
					// do something
				}
				break;
		}
	}

}
