<?php declare(strict_types = 1);

namespace PHPStan\Type;

class UnionIterableType implements IterableType
{

	use ClassTypeHelperTrait, IterableTypeTrait;

	/** @var \PHPStan\Type\Type[] */
	private $otherTypes;

	public function __construct(
		Type $itemType,
		bool $nullable,
		array $otherTypes
	)
	{
		$this->itemType = $itemType;
		$this->nullable = $nullable;
		$this->otherTypes = $otherTypes;
	}

	/**
	 * @return \PHPStan\Type\Type[]
	 */
	public function getOtherTypes(): array
	{
		return $this->otherTypes;
	}

	public function combineWith(Type $otherType): Type
	{
		if ($otherType instanceof IterableType) {
			$otherTypes = $this->getOtherTypes();
			if ($otherType instanceof self) {
				$otherTypesTemp = [];
				foreach ($this->getOtherTypes() as $otherOtherType) {
					$otherTypesTemp[$otherOtherType->describe()] = $otherOtherType;
				}
				foreach ($otherType->getOtherTypes() as $otherOtherType) {
					$otherTypesTemp[$otherOtherType->describe()] = $otherOtherType;
				}

				$otherTypes = array_values($otherTypesTemp);
			}
			return new self(
				$this->getItemType()->combineWith($otherType->getItemType()),
				$this->isNullable() || $otherType->isNullable(),
				$otherTypes
			);
		}

		if ($otherType instanceof NullType) {
			return $this->makeNullable();
		}

		return new MixedType($this->isNullable() || $otherType->isNullable());
	}

	public function makeNullable(): Type
	{
		return new self($this->getItemType(), true, $this->otherTypes);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof MixedType) {
			return true;
		}

		if ($this->isNullable() && $type instanceof NullType) {
			return true;
		}

		if ($type instanceof self) {
			foreach ($type->getOtherTypes() as $otherOtherType) {
				$matchesAtLeastOne = false;
				foreach ($this->getOtherTypes() as $otherType) {
					if ($otherType->accepts($otherOtherType)) {
						$matchesAtLeastOne = true;
						break;
					}
				}
				if (!$matchesAtLeastOne) {
					return false;
				}
			}
		}

		if ($type instanceof IterableType) {
			return $this->getItemType()->accepts($type->getItemType());
		}

		foreach ($this->getOtherTypes() as $otherType) {
			if ($otherType->accepts($type)) {
				return true;
			}
		}

		return false;
	}

	public function describe(): string
	{
		return sprintf('%s[]|%s', $this->getItemType()->describe(), implode('|', array_map(function (Type $otherType): string {
			return $otherType->describe();
		}, $this->otherTypes)));
	}

	public function canAccessProperties(): bool
	{
		foreach ($this->otherTypes as $otherType) {
			if ($otherType->canAccessProperties()) {
				return true;
			}
		}

		return $this->itemType->canAccessProperties();
	}

	public function canCallMethods(): bool
	{
		foreach ($this->otherTypes as $otherType) {
			if ($otherType->canCallMethods()) {
				return true;
			}
		}

		return $this->itemType->canCallMethods();
	}

}
