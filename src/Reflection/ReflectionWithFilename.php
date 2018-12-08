<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

interface ReflectionWithFilename
{

	/**
	 * @return string|false
	 */
	public function getFileName();

}
