<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\NonGenericTypeTrait;
use PHPStan\Type\Traits\ObjectTypeTrait;

class ObjectWithoutClassType implements SubtractableType
{

	use ObjectTypeTrait;
	use NonGenericTypeTrait;

	/** @var \PHPStan\Type\Type|null */
	private $subtractedType;

	public function __construct(
		?Type $subtractedType = null
	)
	{
		$this->subtractedType = $subtractedType;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return TrinaryLogic::createFromBoolean(
			$type instanceof self || $type instanceof TypeWithClassName
		);
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		if ($type instanceof self) {
			if ($this->subtractedType === null) {
				return TrinaryLogic::createYes();
			}
			if ($type->subtractedType !== null) {
				$isSuperType = $type->subtractedType->isSuperTypeOf($this->subtractedType);
				if ($isSuperType->yes()) {
					return TrinaryLogic::createYes();
				}
			}

			return TrinaryLogic::createMaybe();
		}

		if ($type instanceof TypeWithClassName) {
			if ($this->subtractedType === null) {
				return TrinaryLogic::createYes();
			}

			return $this->subtractedType->isSuperTypeOf($type)->negate();
		}

		return TrinaryLogic::createNo();
	}

	public function equals(Type $type): bool
	{
		if (!$type instanceof self) {
			return false;
		}

		if ($this->subtractedType === null) {
			if ($type->subtractedType === null) {
				return true;
			}

			return false;
		}

		if ($type->subtractedType === null) {
			return false;
		}

		return $this->subtractedType->equals($type->subtractedType);
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			static function (): string {
				return 'object';
			},
			static function (): string {
				return 'object';
			},
			function () use ($level): string {
				$description = 'object';
				if ($this->subtractedType !== null) {
					$description .= sprintf('~%s', $this->subtractedType->describe($level));
				}

				return $description;
			}
		);
	}

	public function subtract(Type $type): Type
	{
		if ($type instanceof self) {
			return new NeverType();
		}
		if ($this->subtractedType !== null) {
			$type = TypeCombinator::union($this->subtractedType, $type);
		}

		return new self($type);
	}

	public function getTypeWithoutSubtractedType(): Type
	{
		return new self();
	}

	public function changeSubtractedType(?Type $subtractedType): Type
	{
		return new self($subtractedType);
	}

	public function getSubtractedType(): ?Type
	{
		return $this->subtractedType;
	}


	public function traverse(callable $cb): Type
	{
		$subtractedType = $this->subtractedType !== null ? $cb($this->subtractedType) : null;

		if ($subtractedType !== $this->subtractedType) {
			return new self($subtractedType);
		}

		return $this;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['subtractedType'] ?? null);
	}

}
