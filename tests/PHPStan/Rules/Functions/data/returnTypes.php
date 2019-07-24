<?php

namespace ReturnTypes;

function returnNothing()
{
	return;
}

function returnInteger(): int
{
	return 1;
	return 'foo';
	$foo = function () {
		return 'bar';
	};
}

function returnObject(): Bar
{
	return 1;
	return new Foo();
	return new Bar();
}

function returnChild(): Foo
{
	return new Foo();
	return new FooChild();
	return new OtherInterfaceImpl();
}

/**
 * @return string|null
 */
function returnNullable()
{
	return 'foo';
	return null;
}

function returnInterface(): FooInterface
{
	return new Foo();
}

/**
 * @return void
 */
function returnVoid()
{
	return;
	return null;
	return 1;
}

function returnAlias(): Foo
{
	return new FooAlias();
}

function returnAnotherAlias(): FooAlias
{
	return new Foo();
}

/**
 * @return int
 */
function containsYield()
{
	yield 1;
	return;
}

/**
 * @return mixed[]|string|null
 */
function returnUnionIterable()
{
	if (something()) {
		return 'foo';
	}

	return [];
}

/**
 * @param array<int, int> $arr
 */
function arrayMapConservesNonEmptiness(array $arr) : int {
	if (!$arr) {
		return 5;
	}

	$arr = array_map(function($a) : int { return $a; }, $arr);

	return array_shift($arr);
}
