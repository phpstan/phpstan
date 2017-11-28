<?php declare(strict_types = 1);

namespace PHPStan\Type;

trait IterableTypeTrait
{

	/** @var \PHPStan\Type\Type */
	private $itemType;

	public function getItemType(): Type
	{
		return $this->itemType;
	}

}
