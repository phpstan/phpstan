<?php declare(strict_types = 1);

namespace PHPStan\Type;

trait IterableTypeTrait
{

	/** @var \PHPStan\Type\Type */
	private $itemType;

	public function getNestedItemType(): NestedArrayItemType
	{
		$depth = 0;
		/** @var \PHPStan\Type\Type $itemType */
		$itemType = $this;
		while ($itemType->isIterable() === TrinaryLogic::YES) {
			$itemType = $itemType->getIterableValueType();
			$depth++;
		}

		return new NestedArrayItemType($itemType, $depth);
	}

	public function getItemType(): Type
	{
		return $this->itemType;
	}

	/**
	 * @return string|null
	 */
	public function getClass()
	{
		return null;
	}

	public function canAccessProperties(): bool
	{
		return false;
	}

	public function canCallMethods(): bool
	{
		return false;
	}

}
