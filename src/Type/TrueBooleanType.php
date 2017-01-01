<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TrueBooleanType implements BooleanType
{

	/** @var bool */
	private $nullable;

	public function __construct(bool $nullable)
	{
		$this->nullable = $nullable;
	}

	public function describe(): string
	{
		return 'true' . ($this->nullable ? '|null' : '');
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
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
		if ($otherType instanceof BooleanType) {
			return new TrueOrFalseBooleanType($this->isNullable() || $otherType->isNullable());
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType();
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

		return $type instanceof MixedType;
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

}
