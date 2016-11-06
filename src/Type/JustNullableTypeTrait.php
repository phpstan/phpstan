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
			$thisClass = get_class($this);
			return new $thisClass($this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		$thisClass = get_class($this);
		return new $thisClass(true);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof $this) {
			return true;
		}

		if ($this->isNullable() && $type instanceof NullType) {
			return true;
		}

		return $type instanceof MixedType;
	}

}
