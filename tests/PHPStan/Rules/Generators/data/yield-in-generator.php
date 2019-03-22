<?php // lint <= 7.0

namespace YieldInGenerator;

function doFoo(): \Generator
{
	yield 1;
	yield from doFoo();
}

function doBar(): int
{
	yield 1;
	yield from doFoo();
}

/**
 * @return mixed
 */
function doBaz()
{
	yield 1;
	yield from doFoo();
}

/**
 * @return \Generator|int
 */
function doLorem()
{
	yield 1;
	yield from doFoo();
}

function doIpsum(): array
{
	yield 1;
	yield from doFoo();
}

/**
 * @return \Generator|array
 */
function doDolor()
{
	yield 1;
	yield from doFoo();
}

/**
 * @return array|int
 */
function doSit()
{
	yield 1;
	yield from doFoo();
}

function doAmet(): iterable
{
	yield 1;
	yield from doFoo();
}

/**
 * @return iterable
 */
function doConstecteur()
{
	yield 1;
	yield from doFoo();
}
