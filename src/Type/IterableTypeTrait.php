<?php declare(strict_types = 1);

namespace PHPStan\Type;

trait IterableTypeTrait
{

	/** @var \PHPStan\Type\Type */
	private $itemType;

	/** @var bool */
	private $nullable;

	public function getNestedItemType(): NestedArrayItemType
	{
		$depth = 0;
		/** @var \PHPStan\Type\Type $itemType */
		$itemType = $this;
		while ($itemType instanceof IterableType) {
			$itemType = $itemType->getItemType();
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

	public function isNullable(): bool
	{
		return $this->nullable;
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
