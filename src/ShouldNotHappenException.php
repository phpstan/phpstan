<?php declare(strict_types = 1);

namespace PHPStan;

final class ShouldNotHappenException extends \Exception
{

	public function __construct()
	{
		parent::__construct('Internal error.');
	}

}
