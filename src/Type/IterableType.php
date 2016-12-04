<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface IterableType extends Type
{

	public function getItemType(): Type;

	public function getNestedItemType(): NestedArrayItemType;

}
