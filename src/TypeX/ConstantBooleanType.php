<?php declare(strict_types = 1);

namespace PHPStan\TypeX;

class ConstantBooleanType extends BooleanType implements ConstantScalarType
{
	/** @var bool */
	private $value;

	public function __construct(TypeXFactory $factory, bool $value)
	{
		parent::__construct($factory);
		$this->value = $value;
	}

	public function getValue(): bool
	{
		return $this->value;
	}

	public function generalize(): TypeX
	{
		return $this->factory->createBooleanType();
	}

	public function describe(): string
	{
		return $this->value ? 'true' : 'false';
	}

	public function acceptsX(TypeX $otherType): bool
	{
		return $otherType instanceof self && $otherType->value === $this->value;
	}
}
