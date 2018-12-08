<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

class InaccessibleMethod implements ParametersAcceptor
{

	/** @var MethodReflection */
	private $methodReflection;

	public function __construct(MethodReflection $methodReflection)
	{
		$this->methodReflection = $methodReflection;
	}

	public function getMethod(): MethodReflection
	{
		return $this->methodReflection;
	}

	/**
	 * @return array<int, \PHPStan\Reflection\ParameterReflection>
	 */
	public function getParameters(): array
	{
		return [];
	}

	public function isVariadic(): bool
	{
		return true;
	}

	public function getReturnType(): Type
	{
		return new MixedType();
	}

}
