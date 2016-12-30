<?php declare(strict_types = 1);

namespace PHPStan\Type;

class CommonUnionType implements UnionType
{

	/** @var \PHPStan\Type\Type[] */
	private $types;

	/** @var bool */
	private $isNullable;

	public function __construct(
		array $types,
		bool $isNullable
	)
	{
		$this->types = $types;
		$this->isNullable = $isNullable;
	}

	/**
	 * @return \PHPStan\Type\Type[]
	 */
	public function getTypes(): array
	{
		return $this->types;
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
		return UnionTypeHelper::getReferencedClasses($this->getTypes());
	}

	public function isNullable(): bool
	{
		return $this->isNullable;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

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
			$types,
			$this->isNullable() || $otherType->isNullable()
		);
	}

	public function makeNullable(): Type
	{
		return new self($this->getTypes(), true);
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
		if ($accepts !== null) {
			return $accepts;
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
		return UnionTypeHelper::describe($this->getTypes(), $this->isNullable());
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
		return new self(UnionTypeHelper::resolveStatic($className, $this->getTypes()), $this->isNullable());
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return new self(UnionTypeHelper::changeBaseClass($className, $this->getTypes()), $this->isNullable());
	}

}
