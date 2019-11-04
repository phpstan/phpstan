<?php declare(strict_types = 1);

namespace PHPStan\E2E;

use PhpParser\Node\Scalar\String_;

class PharAutoloaderWorks
{

	public function __construct(String_ $string)
	{
		unset($string);
	}

}

new PharAutoloaderWorks(new String_(''));
