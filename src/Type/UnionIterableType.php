<?php declare(strict_types = 1);

namespace PHPStan\Type;

class UnionIterableType implements IterableType, UnionType
{

	use IterableTypeTrait;

	/** @var \PHPStan\Type\Type[] */
	private $types;

	public function __construct(
		Type $itemType,
		bool $nullable,
		array $types
	)
	{
		$this->itemType = $itemType;
		$this->nullable = $nullable;
		$this->types = $types;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return UnionTypeHelper::getClass($this->getTypes());
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		$classes = UnionTypeHelper::getReferencedClasses($this->getTypes());
		$classes = array_merge($classes, $this->getItemType()->getReferencedClasses());

		return $classes;
	}

	/**
	 * @return \PHPStan\Type\Type[]
	 */
	public function getTypes(): array
	{
		return $this->types;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof IterableType) {
			$types = $this->getTypes();
			if ($otherType instanceof UnionType) {
				$otherTypesTemp = [];
				foreach ($this->getTypes() as $otherOtherType) {
					$otherTypesTemp[$otherOtherType->describe()] = $otherOtherType;
				}
				foreach ($otherType->getTypes() as $otherOtherType) {
					$otherTypesTemp[$otherOtherType->describe()] = $otherOtherType;
				}

				$types = array_values($otherTypesTemp);
			}
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType()),
				$this->isNullable() || $otherType->isNullable(),
				$types
			);
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType();
	}

	public function makeNullable(): Type
	{
		return new self($this->getItemType(), true, $this->types);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof MixedType) {
			return true;
		}

		if ($this->isNullable() && $type instanceof NullType) {
			return true;
		}

		$accepts = UnionTypeHelper::accepts($this, $type);
		if ($accepts !== null && !$accepts) {
			return false;
		}

		if ($type instanceof IterableType) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		foreach ($this->getTypes() as $otherType) {
			if ($otherType->accepts($type)) {
				return true;
			}
		}

		return false;
	}

	public function describe(): string
	{
		return sprintf('%s[]|%s', $this->getItemType()->describe(), UnionTypeHelper::describe($this->getTypes(), $this->isNullable()));
	}

	public function canAccessProperties(): bool
	{
		return UnionTypeHelper::canAccessProperties($this->getTypes());
	}

	public function canCallMethods(): bool
	{
		return UnionTypeHelper::canCallMethods($this->getTypes());
	}

	public function isDocumentableNatively(): bool
	{
		return false;
	}

	public function resolveStatic(string $className): Type
	{
		$itemType = $this->getItemType();
		if ($itemType instanceof StaticResolvableType) {
			$itemType = $itemType->resolveStatic($className);
		}

		return new self(
			$itemType,
			$this->isNullable(),
			UnionTypeHelper::resolveStatic($className, $this->getTypes())
		);
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		$itemType = $this->getItemType();
		if ($itemType instanceof StaticResolvableType) {
			$itemType = $itemType->changeBaseClass($className);
		}

		return new self(
			$itemType,
			$this->isNullable(),
			UnionTypeHelper::changeBaseClass($className, $this->getTypes())
		);
	}

}
