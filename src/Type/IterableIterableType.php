<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class IterableIterableType implements StaticResolvableType
{

	use IterableTypeTrait;

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
		if ($otherType->isIterable()->yes()) {
			return new self(
				$this->getIterableValueType()->combineWith($otherType->getIterableValueType())
			);
		}

		return TypeCombinator::combine($this, $otherType);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		if ($type->isIterable()->yes()) {
			return $this->getIterableValueType()->accepts($type->getIterableValueType());
		}

		return false;
	}

	public function describe(): string
	{
		if ($this->getItemType() instanceof UnionType) {
			$description = implode('|', array_map(function (Type $type): string {
				return sprintf('%s[]', $type->describe());
			}, $this->getItemType()->getTypes()));
		} else {
			$description = sprintf('%s[]', $this->getItemType()->describe());
		}
		return sprintf('iterable(%s)', $description);
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

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getIterableKeyType(): Type
	{
		return new MixedType();
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
		return new self($properties['itemType']);
	}

}
