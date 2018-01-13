<?php

namespace InvalidPhpDoc;

/**
 * @param
 * @param $invalid
 * @param $invalid Foo
 * @param A & B | C $paramNameA
 * @param (A & B $paramNameB
 * @param ~A & B $paramNameC
 *
 * @var
 * @var $invalid
 * @var $invalid Foo
 *
 * @return
 * @return [int, string]
 * @return A & B | C
 *
 * @param Foo $valid
 * @return Foo
 */
function foo()
{

	/** @var \\Foo|\Bar $test */
	$test = doFoo();

}

/**
 * This is a class description that talks about some phpDoc @param and continues
 * to talk about it even more
 */
class Foo
{

}
