<?php declare(strict_types = 1);

namespace PHPStan\Type;

class UnionIterableType implements UnionType
{

	use IterableTypeTrait;

	/** @var \PHPStan\Type\Type[] */
	private $types;

	/**
	 * @param Type $itemType
	 * @param Type[] $types
	 */
	public function __construct(
		Type $itemType,
		array $types
	)
	{
		$this->itemType = $itemType;

		$throwException = function () use ($itemType, $types) {
			throw new \PHPStan\ShouldNotHappenException(sprintf(
				'Cannot create %s with: %s, %s',
				self::class,
				$itemType->describe(),
				implode(', ', array_map(function (Type $type): string {
					return $type->describe();
				}, $types))
			));
		};
		if (count($types) < 1) {
			$throwException();
		}
		foreach ($types as $type) {
			if ($type instanceof UnionType) {
				$throwException();
			}
		}
		$this->types = UnionTypeHelper::sortTypes($types);
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
		return TypeCombinator::combine($this, $otherType);
	}

	public function accepts(Type $type): bool
	{
		$accepts = UnionTypeHelper::accepts($this, $type);
		if ($accepts !== null && !$accepts) {
			return false;
		}

		if ($type->isIterable() === TrinaryLogic::YES) {
			return $this->getItemType()->accepts($type->getIterableValueType());
		}

		if (TypeCombinator::shouldSkipUnionTypeAccepts($this)) {
			return true;
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
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
		$format = $this->getItemType() instanceof UnionType ? '(%s)[]|%s' : '%s[]|%s';
		return sprintf($format, $this->getItemType()->describe(), UnionTypeHelper::describe($this->getTypes()));
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
			UnionTypeHelper::changeBaseClass($className, $this->getTypes())
		);
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

	public static function __set_state(array $properties): Type
	{
		return new self($properties['itemType'], $properties['types']);
	}

}
