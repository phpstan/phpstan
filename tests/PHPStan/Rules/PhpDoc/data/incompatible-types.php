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
 * @param string[] ...$strings valid according to PhpStorm, but actually invalid (accepted for now)
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
function testReturnIntNotSubType(): int
{

}

/**
 * @param string[] $strings
 */
function anotherVariadicStrings(string ...$strings)
{

}

/**
 * @param int[] $strings
 */
function incompatibleVariadicStrings(string ...$strings)
{

}

/**
 * @param string ...$numbers
 */
function incompatibleVariadicNumbers(int ...$numbers)
{

}

/**
 * @param string[] ...$strings
 */
function variadicStringArrays(array ...$strings)
{

}
