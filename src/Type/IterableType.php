<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface IterableType extends StaticResolvableType
{

	public function getItemType(): Type;

	public function getNestedItemType(): NestedArrayItemType;

}
