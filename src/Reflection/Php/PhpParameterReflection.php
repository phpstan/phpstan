<?php

namespace PHPStan\Reflection\Php;

use PHPStan\Reflection\ParameterReflection;

class PhpParameterReflection implements ParameterReflection
{

	/** @var \ReflectionParameter */
	private $reflection;

	public function __construct(\ReflectionParameter $reflection)
	{
		$this->reflection = $reflection;
	}

	public function isOptional(): bool
	{
		return $this->reflection->isOptional();
	}

}
