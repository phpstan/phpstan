<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\MaybeObjectTypeTrait;
use PHPStan\Type\Traits\MaybeOffsetAccessibleTypeTrait;
use PHPStan\Type\Traits\NonGenericTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;

class IterableType implements StaticResolvableType, CompoundType
{

	use MaybeCallableTypeTrait;
	use MaybeObjectTypeTrait;
	use MaybeOffsetAccessibleTypeTrait;
	use UndecidedBooleanTypeTrait;
	use NonGenericTypeTrait;

	/** @var \PHPStan\Type\Type */
	private $keyType;

	/** @var \PHPStan\Type\Type */
	private $itemType;

	public function __construct(
		Type $keyType,
		Type $itemType
	)
	{
		$this->keyType = $keyType;
		$this->itemType = $itemType;
	}

	public function getItemType(): Type
	{
		return $this->itemType;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return array_merge(
			$this->keyType->getReferencedClasses(),
			$this->getItemType()->getReferencedClasses()
		);
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		if ($type->isIterable()->yes()) {
			return $this->getIterableValueType()->accepts($type->getIterableValueType(), $strictTypes)
				->and($this->getIterableKeyType()->accepts($type->getIterableKeyType(), $strictTypes));
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this, $strictTypes);
		}

		return TrinaryLogic::createNo();
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return $type->isIterable()
			->and($this->getIterableValueType()->isSuperTypeOf($type->getIterableValueType()))
			->and($this->getIterableKeyType()->isSuperTypeOf($type->getIterableKeyType()));
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof IntersectionType || $otherType instanceof UnionType) {
			return $otherType->isSuperTypeOf(new UnionType([
				new ArrayType($this->keyType, $this->itemType),
				new IntersectionType([
					new ObjectType(\Traversable::class),
					$this,
				]),
			]));
		}

		if ($otherType instanceof self) {
			$limit = TrinaryLogic::createYes();
		} else {
			$limit = TrinaryLogic::createMaybe();
		}

		return $limit->and(
			$otherType->isIterable(),
			$otherType->getIterableValueType()->isSuperTypeOf($this->itemType),
			$otherType->getIterableKeyType()->isSuperTypeOf($this->keyType)
		);
	}

	public function isAcceptedBy(Type $acceptingType, bool $strictTypes): TrinaryLogic
	{
		return $this->isSubTypeOf($acceptingType);
	}

	public function equals(Type $type): bool
	{
		if (!$type instanceof self) {
			return false;
		}

		return $this->keyType->equals($type->keyType)
			&& $this->itemType->equals($type->itemType);
	}

	public function describe(VerbosityLevel $level): string
	{
		if ($this->keyType instanceof MixedType) {
			if ($this->itemType instanceof MixedType) {
				return 'iterable';
			}

			return sprintf('iterable<%s>', $this->itemType->describe($level));
		}

		return sprintf('iterable<%s, %s>', $this->keyType->describe($level), $this->itemType->describe($level));
	}

	public function toNumber(): Type
	{
		return new ErrorType();
	}

	public function toString(): Type
	{
		return new ErrorType();
	}

	public function toInteger(): Type
	{
		return new ErrorType();
	}

	public function toFloat(): Type
	{
		return new ErrorType();
	}

	public function toArray(): Type
	{
		return new ArrayType($this->keyType, $this->getItemType());
	}

	public function resolveStatic(string $className): Type
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->keyType,
				$this->getItemType()->resolveStatic($className)
			);
		}

		return $this;
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->keyType,
				$this->getItemType()->changeBaseClass($className)
			);
		}

		return $this;
	}

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function isIterableAtLeastOnce(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function getIterableKeyType(): Type
	{
		return $this->keyType;
	}

	public function getIterableValueType(): Type
	{
		return $this->getItemType();
	}

	public function isArray(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public function traverse(callable $cb): Type
	{
		$keyType = $cb($this->keyType);
		$itemType = $cb($this->itemType);

		if ($keyType !== $this->keyType || $itemType !== $this->itemType) {
			return new self($keyType, $itemType);
		}

		return $this;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['keyType'], $properties['itemType']);
	}

}
