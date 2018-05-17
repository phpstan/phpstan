<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Type\Type;

class FunctionVariant implements ParametersAcceptor
{

	/** @var array<int, ParameterReflection> */
	private $parameters;

	/** @var bool */
	private $isVariadic;

	/** @var Type */
	private $returnType;

	/**
	 * @param array<int, ParameterReflection> $parameters
	 * @param bool $isVariadic
	 * @param Type $returnType
	 */
	public function __construct(
		array $parameters,
		bool $isVariadic,
		Type $returnType
	)
	{
		$this->parameters = $parameters;
		$this->isVariadic = $isVariadic;
		$this->returnType = $returnType;
	}

	/**
	 * @return array<int, ParameterReflection>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		return $this->isVariadic;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

}
