<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\TrinaryLogic;

class IntersectionType implements CompoundType, StaticResolvableType
{

	/** @var \PHPStan\Type\Type[] */
	private $types;

	/**
	 * @param Type[] $types
	 */
	public function __construct(array $types)
	{
		$this->types = UnionTypeHelper::sortTypes($types);
	}

	/**
	 * @return Type[]
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
		return UnionTypeHelper::getClass($this->types);
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return UnionTypeHelper::getReferencedClasses($this->types);
	}

	public function combineWith(Type $otherType): Type
	{
		return TypeCombinator::union($this, $otherType);
	}

	public function accepts(Type $otherType): bool
	{
		foreach ($this->types as $type) {
			if (!$type->accepts($otherType)) {
				return false;
			}
		}

		return true;
	}

	public function isSupersetOf(Type $otherType): TrinaryLogic
	{
		$results = [];
		foreach ($this->getTypes() as $innerType) {
			$results[] = $innerType->isSupersetOf($otherType);
		}

		return TrinaryLogic::createYes()->and(...$results);
	}

	public function isSubsetOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof self || $otherType instanceof UnionType) {
			return $otherType->isSupersetOf($this);
		}

		$results = [];
		foreach ($this->getTypes() as $innerType) {
			$results[] = $otherType->isSupersetOf($innerType);
		}

		return TrinaryLogic::maxMin(...$results);
	}

	public function describe(): string
	{
		$typeNames = [];

		foreach ($this->types as $type) {
			$typeNames[] = $type->describe();
		}

		return implode('&', $typeNames);
	}

	public function canAccessProperties(): bool
	{
		$result = $this->intersectResults(function (Type $type): TrinaryLogic {
			return $type->canAccessProperties() ? TrinaryLogic::createYes() : TrinaryLogic::createNo();
		});

		return $result->yes();
	}

	public function canCallMethods(): bool
	{
		$result = $this->intersectResults(function (Type $type): TrinaryLogic {
			return $type->canCallMethods() ? TrinaryLogic::createYes() : TrinaryLogic::createNo();
		});

		return $result->yes();
	}

	public function isDocumentableNatively(): bool
	{
		return false;
	}

	public function isIterable(): TrinaryLogic
	{
		return $this->intersectResults(function (Type $type): TrinaryLogic {
			return $type->isIterable();
		});
	}

	public function getIterableKeyType(): Type
	{
		return $this->intersectTypes(function (Type $type): Type {
			return $type->getIterableKeyType();
		});
	}

	public function getIterableValueType(): Type
	{
		return $this->intersectTypes(function (Type $type): Type {
			return $type->getIterableValueType();
		});
	}

	public function isCallable(): TrinaryLogic
	{
		return $this->intersectResults(function (Type $type): TrinaryLogic {
			return $type->isCallable();
		});
	}

	public function resolveStatic(string $className): Type
	{
		return new self(UnionTypeHelper::resolveStatic($className, $this->getTypes()));
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		return new self(UnionTypeHelper::changeBaseClass($className, $this->getTypes()));
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['types']);
	}

	private function intersectResults(callable $getResult): TrinaryLogic
	{
		$operands = array_map($getResult, $this->types);
		return TrinaryLogic::maxMin(...$operands);
	}

	private function intersectTypes(callable $getType): Type
	{
		$operands = array_map($getType, $this->types);
		return TypeCombinator::intersect(...$operands);
	}

}
