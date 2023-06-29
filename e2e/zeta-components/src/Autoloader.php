<?php

declare(strict_types=1);

namespace App;

use ezcBase;
use ezcBaseAutoloadException;

final class Autoloader
{

	/**
	 * @throws ezcBaseAutoloadException
	 */
	public static function autoloadEzc(string $className): void
	{
		ezcBase::autoload($className);
	}

}
