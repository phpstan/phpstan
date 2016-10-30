<?php declare(strict_types = 1);

namespace PHPStan\Type;

trait JustNullableTypeTrait
{

	/** @var bool */
	private $nullable;

	public function __construct(bool $nullable)
	{
		$this->nullable = $nullable;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof $this) {
			return new self($this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self(true);
	}

}
