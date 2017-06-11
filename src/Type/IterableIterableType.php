<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IterableIterableType implements StaticResolvableType
{

	use ClassTypeHelperTrait, IterableTypeTrait;

	public function __construct(
		Type $itemType
	)
	{
		$this->itemType = $itemType;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return $this->getItemType()->getReferencedClasses();
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType->isIterable() === TrinaryLogic::YES) {
			return new self(
				$this->getIterableValueType()->combineWith($otherType->getIterableValueType())
			);
		}

		return TypeCombinator::combine($this, $otherType);
	}

	public function accepts(Type $type): bool
	{
		if ($type->isIterable() === TrinaryLogic::YES) {
			return $this->getIterableValueType()->accepts($type->getIterableValueType());
		}

		if ($type->getClass() !== null && $this->exists($type->getClass())) {
			$classReflection = new \ReflectionClass($type->getClass());
			return $classReflection->implementsInterface(\Traversable::class);
		}

		if ($type instanceof MixedType) {
			return true;
		}

		if ($type instanceof UnionType) {
			return UnionTypeHelper::acceptsAll($this, $type);
		}

		return false;
	}

	public function describe(): string
	{
		return sprintf('iterable(%s[])', $this->getItemType()->describe());
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function resolveStatic(string $className): Type
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->getItemType()->resolveStatic($className)
			);
		}

		return $this;
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->getItemType()->changeBaseClass($className)
			);
		}

		return $this;
	}

	public function isIterable(): int
	{
		return TrinaryLogic::YES;
	}

	public function getIterableKeyType(): Type
	{
		return new MixedType();
	}

	public function getIterableValueType(): Type
	{
		return $this->getItemType();
	}

}
