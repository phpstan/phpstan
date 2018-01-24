<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class IterableIterableType implements StaticResolvableType, CompoundType
{

	use IterableTypeTrait;

	/**
	 * @var \PHPStan\Type\Type
	 */
	private $keyType;

	public function __construct(
		Type $keyType,
		Type $itemType
	)
	{
		$this->keyType = $keyType;
		$this->itemType = $itemType;
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

	public function accepts(Type $type): bool
	{
		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		if ($type->isIterable()->yes()) {
			return $this->getIterableValueType()->accepts($type->getIterableValueType())
				&& $this->getIterableKeyType()->accepts($type->getIterableKeyType());
		}

		return false;
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

	public function describe(): string
	{
		if ($this->keyType instanceof MixedType) {
			if ($this->itemType instanceof MixedType) {
				return 'iterable';
			}

			return sprintf('iterable<%s>', $this->itemType->describe());
		}

		return sprintf('iterable<%s, %s>', $this->keyType->describe(), $this->itemType->describe());
	}

	public function isDocumentableNatively(): bool
	{
		return true;
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

	public function getIterableKeyType(): Type
	{
		return $this->keyType;
	}

	public function getIterableValueType(): Type
	{
		return $this->getItemType();
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['keyType'], $properties['itemType']);
	}

}
