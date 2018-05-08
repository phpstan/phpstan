<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Reflection\FunctionVariant;
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

	/** @var \PHPStan\Type\Type|null */
	private $throwType;

	/** @var bool */
	private $isDeprecated;

	/**
	 * @param string $name
	 * @param \PHPStan\Reflection\Native\NativeParameterReflection[] $parameters
	 * @param bool $variadic
	 * @param \PHPStan\Type\Type $returnType
	 * @param \PHPStan\Type\Type|null $throwType
	 * @param bool $isDeprecated
	 */
	public function __construct(
		string $name,
		array $parameters,
		bool $variadic,
		Type $returnType,
		?Type $throwType,
		bool $isDeprecated
	)
	{
		$this->name = $name;
		$this->parameters = $parameters;
		$this->variadic = $variadic;
		$this->returnType = $returnType;
		$this->throwType = $throwType;
		$this->isDeprecated = $isDeprecated;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array
	{
		return [
			new FunctionVariant($this->parameters, $this->variadic, $this->returnType),
		];
	}

	public function getThrowType(): ?Type
	{
		return $this->throwType;
	}

	public function isDeprecated(): bool
	{
		return $this->isDeprecated;
	}

}
