<?php declare(strict_types = 1);

namespace PHPStan\Type;

class IterableIterableType implements IterableType
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
		if ($otherType instanceof IterableType) {
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType())
			);
		}

		return TypeCombinator::combine($this, $otherType);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof IterableType) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		if ($type->getClass() !== null && $this->exists($type->getClass())) {
			$classReflection = new \ReflectionClass($type->getClass());
			return $classReflection->implementsInterface(\Traversable::class);
		}

		if ($type instanceof MixedType) {
			return true;
		}

		if ($type instanceof UnionType && UnionTypeHelper::acceptsAll($this, $type)) {
			return true;
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

}
