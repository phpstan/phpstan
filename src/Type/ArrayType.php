<?php declare(strict_types = 1);

namespace PHPStan\Type;

class ArrayType implements IterableType
{

	use IterableTypeTrait;

	/** @var bool */
	private $itemTypeInferredFromLiteralArray;

	/** @var bool */
	private $possiblyCallable;

	public function __construct(
		Type $itemType,
		bool $nullable,
		bool $itemTypeInferredFromLiteralArray = false,
		bool $possiblyCallable = false
	)
	{
		$this->itemType = $itemType;
		$this->nullable = $nullable;
		$this->itemTypeInferredFromLiteralArray = $itemTypeInferredFromLiteralArray;
		$this->possiblyCallable = $possiblyCallable;
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

	public function isPossiblyCallable(): bool
	{
		return $this->possiblyCallable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof IterableType) {
			$isItemInferredFromLiteralArray = $this->isItemTypeInferredFromLiteralArray();
			$isPossiblyCallable = $this->isPossiblyCallable();
			if ($otherType instanceof self) {
				$isItemInferredFromLiteralArray = $isItemInferredFromLiteralArray || $otherType->isItemTypeInferredFromLiteralArray();
				$isPossiblyCallable = $isPossiblyCallable || $otherType->isPossiblyCallable();
			}
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType()),
				$this->isNullable() || $otherType->isNullable(),
				$isItemInferredFromLiteralArray,
				$isPossiblyCallable
			);
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType();
	}

	public function makeNullable(): Type
	{
		return new self($this->getItemType(), true, $this->isItemTypeInferredFromLiteralArray(), $this->isPossiblyCallable());
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		if ($type instanceof MixedType) {
			return true;
		}

		if ($this->isNullable() && $type instanceof NullType) {
			return true;
		}

		if ($type instanceof UnionType && UnionTypeHelper::acceptsAll($this, $type)) {
			return true;
		}

		return false;
	}

	public function describe(): string
	{
		return sprintf('%s[]', $this->getItemType()->describe()) . ($this->nullable ? '|null' : '');
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
				$this->isNullable(),
				$this->isItemTypeInferredFromLiteralArray(),
				$this->isPossiblyCallable()
			);
		}

		return $this;
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->getItemType()->changeBaseClass($className),
				$this->isNullable(),
				$this->isItemTypeInferredFromLiteralArray(),
				$this->isPossiblyCallable()
			);
		}

		return $this;
	}

}
