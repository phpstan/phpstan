<?php

namespace InvalidPhpDoc;

/**
 * @param string $unknown
 * @param int $a
 * @param array $b
 * @param array $c
 * @param int|float $d
 */
function paramTest(int $a, string $b, iterable $c, int $d)
{

}


/**
 * @param int ...$numbers invalid according to PhpStorm, but actually valid
 */
function variadicNumbers(int ...$numbers)
{

}


/**
 * @param string[] ...$strings valid according to PhpStorm, but actually invalid
 */
function variadicStrings(string ...$strings)
{

}


/**
 * @return int
 */
function testReturnIntOk(): int
{

}


/**
 * @return bool
 */
function testReturnBoolOk(): bool
{

}


/**
 * @return true
 */
function testReturnTrueOk(): bool
{

}


/**
 * @return string
 */
function testReturnIntInvalid(): int
{

}


/**
 * @return string|int
 */
function testReturnIntNotSubset(): int
{

}
