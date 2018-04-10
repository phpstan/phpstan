<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Type\Type;

class NativeFunctionReflection implements \PHPStan\Reflection\FunctionReflection
{

	/** @var string */
	private $name;

	/** @var \PHPStan\Reflection\Native\NativeParameterReflection[] */
	private $parameters;

	/** @var bool */
	private $variadic;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/** @var bool */
	private $isDeprecated;

	/**
	 * @param string $name
	 * @param \PHPStan\Reflection\Native\NativeParameterReflection[] $parameters
	 * @param bool $variadic
	 * @param \PHPStan\Type\Type $returnType
	 * @param bool $isDeprecated
	 */
	public function __construct(
		string $name,
		array $parameters,
		bool $variadic,
		Type $returnType,
		bool $isDeprecated
	)
	{
		$this->name = $name;
		$this->parameters = $parameters;
		$this->variadic = $variadic;
		$this->returnType = $returnType;
		$this->isDeprecated = $isDeprecated;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return \PHPStan\Reflection\ParameterReflection[]
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function isVariadic(): bool
	{
		return $this->variadic;
	}

	public function getReturnType(): Type
	{
		return $this->returnType;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

}
