<?php declare(strict_types = 1);

namespace PHPStan\Reflection\SignatureMap;

use PHPStan\Type\Type;

class FunctionSignature
{

	/** @var \PHPStan\Reflection\SignatureMap\ParameterSignature[] */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/** @var bool */
	private $variadic;

	/**
	 * @param array<int, \PHPStan\Reflection\SignatureMap\ParameterSignature> $parameters
	 * @param \PHPStan\Type\Type $returnType
	 * @param bool $variadic
	 */
	public function __construct(
		array $parameters,
		Type $returnType,
		bool $variadic
	)
	{
		$this->parameters = $parameters;
		$this->returnType = $returnType;
		$this->variadic = $variadic;
	}

	/**
	 * @return array<int, \PHPStan\Reflection\SignatureMap\ParameterSignature>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
	}

}
