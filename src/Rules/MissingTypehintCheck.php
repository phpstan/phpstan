<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;

class MissingTypehintCheck
{

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Type[]
	 */
	public function getIterableTypesWithMissingValueTypehint(Type $type): array
	{
		$iterablesWithMissingValueTypehint = [];
		TypeTraverser::map($type, static function (Type $type, callable $traverse) use (&$iterablesWithMissingValueTypehint): Type {
			if ($type->isIterable()->yes()) {
				$iterableValue = $type->getIterableValueType();
				if ($iterableValue instanceof MixedType && !$iterableValue->isExplicitMixed()) {
					$iterablesWithMissingValueTypehint[] = $type;
				}
				return $type;
			}
			return $traverse($type);
		});

		return $iterablesWithMissingValueTypehint;
	}

}
