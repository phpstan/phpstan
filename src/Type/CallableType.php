<?php declare(strict_types = 1);

namespace PHPStan\Type;

class CallableType implements Type
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
		if ($otherType instanceof self) {
			return new self($this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof ArrayType && $otherType->isPossiblyCallable()) {
			return $this;
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

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return true;
		}

		if ($this->isNullable() && $type instanceof NullType) {
			return true;
		}

		if ($type instanceof ArrayType && $type->isPossiblyCallable()) {
			return true;
		}

		if ($type->getClass() === 'Closure') {
			return true;
		}

		return $type instanceof MixedType;
	}

	public function describe(): string
	{
		return 'callable';
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return true;
	}

}
