<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class NativeFunctionReflection implements \PHPStan\Reflection\FunctionReflection
{

	/** @var string */
	private $name;

	/** @var \PHPStan\Reflection\ParametersAcceptor[] */
	private $variants;

	/** @var \PHPStan\Type\Type|null */
	private $throwType;

	/** @var TrinaryLogic */
	private $hasSideEffects;

	/**
	 * @param string $name
	 * @param \PHPStan\Reflection\ParametersAcceptor[] $variants
	 * @param \PHPStan\Type\Type|null $throwType
	 * @param \PHPStan\TrinaryLogic $hasSideEffects
	 */
	public function __construct(
		string $name,
		array $variants,
		?Type $throwType,
		TrinaryLogic $hasSideEffects
	)
	{
		$this->name = $name;
		$this->variants = $variants;
		$this->throwType = $throwType;
		$this->hasSideEffects = $hasSideEffects;
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

	public function isDeprecated(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isInternal(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isFinal(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function hasSideEffects(): TrinaryLogic
	{
		return $this->hasSideEffects;
	}

}
