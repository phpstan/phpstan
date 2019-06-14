<?php

namespace ValidPhpDoc;

class Foo
{
	/**
	 * @param    array<    array<int,float>,    array< int,    string>,    int  > $x single long line
	 * @param array< array<int,float>,      array< \DateTime,
	 * string>> $y Multiline
	 */
	function bar($x, $y)
	{
	}
}

/**
 * @var    array<
 *   array<int,float>,
 *   array< int,
 *   string>,
 *   int
 * > $x The line
 *  with the
 *      breaks
 */
function baz($x, $y)
{
}
