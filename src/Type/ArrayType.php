<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class ArrayType implements StaticResolvableType
{

	use IterableTypeTrait;

	/** @var \PHPStan\Type\Type */
	private $keyType;

	/** @var bool */
	private $itemTypeInferredFromLiteralArray;

	/** @var TrinaryLogic */
	private $callable;

	public function __construct(
		Type $keyType,
		Type $itemType,
		bool $itemTypeInferredFromLiteralArray = false,
		TrinaryLogic $callable = null
	)
	{
		if ($itemType instanceof UnionType && !TypeCombinator::isUnionTypesEnabled()) {
			$itemType = new MixedType();
		}
		$this->keyType = $keyType;
		$this->itemType = $itemType;
		$this->itemTypeInferredFromLiteralArray = $itemTypeInferredFromLiteralArray;
		$this->callable = $callable ?? TrinaryLogic::createMaybe()->and((new StringType)->isSuperTypeOf($itemType));
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

	public static function createDeepArrayType(NestedArrayItemType $nestedItemType, bool $nullable): self
	{
		$itemType = $nestedItemType->getItemType();
		for ($i = 0; $i < $nestedItemType->getDepth() - 1; $i++) {
			$itemType = new self(new MixedType(), $itemType, false);
		}

		return new self(new MixedType(), $itemType, $nullable);
	}

	public function isItemTypeInferredFromLiteralArray(): bool
	{
		return $this->itemTypeInferredFromLiteralArray;
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return $this->getItemType()->accepts($type->getItemType())
				&& $this->keyType->accepts($type->keyType);
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		return false;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return $this->getItemType()->isSuperTypeOf($type->getItemType())
				->and($this->keyType->isSuperTypeOf($type->keyType));
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function describe(): string
	{
		if ($this->keyType instanceof MixedType) {
			if ($this->itemType instanceof MixedType) {
				return 'array';
			}

			return sprintf('array<%s>', $this->itemType->describe());
		}

		return sprintf('array<%s, %s>', $this->keyType->describe(), $this->itemType->describe());
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
				$this->keyType,
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
		return $this->keyType;
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
		return new self(
			$properties['keyType'],
			$properties['itemType'],
			$properties['itemTypeInferredFromLiteralArray'],
			$properties['callable']
		);
	}

}
