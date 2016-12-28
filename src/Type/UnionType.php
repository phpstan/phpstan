<?php declare(strict_types = 1);

namespace PHPStan\Type;

interface UnionType extends StaticResolvableType
{

	/**
	 * @return \PHPStan\Type\Type[]
	 */
	public function getTypes(): array;

}
