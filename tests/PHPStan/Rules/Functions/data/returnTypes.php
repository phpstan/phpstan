<?php

namespace ReturnTypes;

function returnNothing()
{
	return;
}

function returnInteger(): int
{
	if (rand(0, 1)) {
		return 1;
	}

	if (rand(0, 1)) {
		return 'foo';
	}
	$foo = function () {
		return 'bar';
	};
}

function returnObject(): Bar
{
	if (rand(0, 1)) {
		return 1;
	}

	if (rand(0, 1)) {
		return new Foo();
	}

	if (rand(0, 1)) {
		return new Bar();
	}
}

function returnChild(): Foo
{
	if (rand(0, 1)) {
		return new Foo();
	}

	if (rand(0, 1)) {
		return new FooChild();
	}

	if (rand(0, 1)) {
		return new OtherInterfaceImpl();
	}
}

/**
 * @return string|null
 */
function returnNullable()
{
	if (rand(0, 1)) {
		return 'foo';
	}

	if (rand(0, 1)) {
		return null;
	}
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
	if (rand(0, 1)) {
		return;
	}

	if (rand(0, 1)) {
		return null;
	}

	if (rand(0, 1)) {
		return 1;
	}
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
