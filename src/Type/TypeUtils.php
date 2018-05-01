<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Constant\ConstantArrayType;

class TypeUtils
{

	/**
	 * @param \PHPStan\Type\Type $type
	 * @return \PHPStan\Type\Constant\ConstantArrayType[]
	 */
	public static function getConstantArrays(Type $type): array
	{
		if ($type instanceof ConstantArrayType) {
			return [$type];
		}

		if ($type instanceof UnionType) {
			$constantArrays = [];
			foreach ($type->getTypes() as $innerType) {
				if (!$innerType instanceof ConstantArrayType) {
					return [];
				}

				$constantArrays[] = $innerType;
			}

			return $constantArrays;
		}

		return [];
	}

}
