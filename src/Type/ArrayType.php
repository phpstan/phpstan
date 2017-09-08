<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ArrayType implements StaticResolvableType
{

	use IterableTypeTrait;

	/** @var bool */
	private $itemTypeInferredFromLiteralArray;

	/** @var TrinaryLogic */
	private $callable;

	public function __construct(
		Type $itemType,
		bool $itemTypeInferredFromLiteralArray = false,
		TrinaryLogic $callable = null
	)
	{
		if ($itemType instanceof UnionType && !TypeCombinator::isUnionTypesEnabled()) {
			$itemType = new MixedType();
		}
		$this->itemType = $itemType;
		$this->itemTypeInferredFromLiteralArray = $itemTypeInferredFromLiteralArray;
		$this->callable = $callable ?? TrinaryLogic::createNo();
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return $this->getItemType()->getReferencedClasses();
	}

	public static function createDeepArrayType(NestedArrayItemType $nestedItemType, bool $nullable): self
	{
		$itemType = $nestedItemType->getItemType();
		for ($i = 0; $i < $nestedItemType->getDepth() - 1; $i++) {
			$itemType = new self($itemType, false);
		}

		return new self($itemType, $nullable);
	}

	public function isItemTypeInferredFromLiteralArray(): bool
	{
		return $this->itemTypeInferredFromLiteralArray;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType->isIterable()->yes()) {
			$isItemInferredFromLiteralArray = $this->isItemTypeInferredFromLiteralArray();
			$callable = $this->callable;
			if ($otherType instanceof self) {
				$isItemInferredFromLiteralArray = $isItemInferredFromLiteralArray || $otherType->isItemTypeInferredFromLiteralArray();
				$callable = $this->callable->and($otherType->callable);
			}
			return new self(
				$this->getIterableValueType()->combineWith($otherType->getIterableValueType()),
				$isItemInferredFromLiteralArray,
				$callable
			);
		}

		return TypeCombinator::union($this, $otherType);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		return false;
	}

	public function isSupersetOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return $this->getItemType()->isSupersetOf($type->getItemType());
		}

		if ($type instanceof CompoundType) {
			return $type->isSubsetOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function describe(): string
	{
		$format = $this->itemType instanceof UnionType ? '(%s)[]' : '%s[]';
		return sprintf($format, $this->getItemType()->describe());
	}

	public function isDocumentableNatively(): bool
	{
		return true;
	}

	public function resolveStatic(string $className): Type
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->getItemType()->resolveStatic($className),
				$this->isItemTypeInferredFromLiteralArray(),
				$this->callable
			);
		}

		return $this;
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->getItemType()->changeBaseClass($className),
				$this->isItemTypeInferredFromLiteralArray(),
				$this->callable
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
		return $this->callable;
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['itemType'], $properties['itemTypeInferredFromLiteralArray'], $properties['callable']);
	}

}
