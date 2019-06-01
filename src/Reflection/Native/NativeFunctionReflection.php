<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Type\Type;

class NativeFunctionReflection implements \PHPStan\Reflection\FunctionReflection
{

	/** @var string */
	private $name;

	/** @var \PHPStan\Reflection\ParametersAcceptor[] */
	private $variants;

	/** @var \PHPStan\Type\Type|null */
	private $throwType;

	/**
	 * @param string $name
	 * @param \PHPStan\Reflection\ParametersAcceptor[] $variants
	 * @param \PHPStan\Type\Type|null $throwType
	 */
	public function __construct(
		string $name,
		array $variants,
		?Type $throwType
	)
	{
		$this->name = $name;
		$this->variants = $variants;
		$this->throwType = $throwType;
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
		return $this->variants;
	}

	public function getThrowType(): ?Type
	{
		return $this->throwType;
	}

	public function getDeprecatedDescription(): ?string
	{
		return null;
	}

	public function isDeprecated(): bool
	{
		return false;
	}

	public function isInternal(): bool
	{
		return false;
	}

	public function isFinal(): bool
	{
		return false;
	}

}
