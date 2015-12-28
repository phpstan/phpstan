<?php declare(strict_types=1);

namespace PHPStan\Type;

class ObjectType implements Type
{

	/** @var string */
	private $class;

	/** @var bool */
	private $nullable;

	public function __construct(string $class, bool $nullable)
	{
		$this->class = $class;
		$this->nullable = $nullable;
	}

	public function getClass()
	{
		return $this->class;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof $this && $this->getClass() == $otherType->getClass()) {
			return new self($this->getClass(), $this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return new self($this->getClass(), true);
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->getClass(), true);
	}

}
