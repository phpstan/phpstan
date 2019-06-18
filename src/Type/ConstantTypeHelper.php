<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;

class ConstantTypeHelper
{

	/**
	 * @param mixed $value
	 */
	public static function getTypeFromValue($value): Type
	{
		if (is_int($value)) {
			return new ConstantIntegerType($value);
		} elseif (is_float($value)) {
			return new ConstantFloatType($value);
		} elseif (is_bool($value)) {
			return new ConstantBooleanType($value);
		} elseif ($value === null) {
			return new NullType();
		} elseif (is_string($value)) {
			return new ConstantStringType($value);
		} elseif (is_array($value)) {
			$arrayBuilder = ConstantArrayTypeBuilder::createEmpty();
			foreach ($value as $k => $v) {
				$arrayBuilder->setOffsetValueType(self::getTypeFromValue($k), self::getTypeFromValue($v));
			}
			return $arrayBuilder->getArray();
		}

		return new MixedType();
	}

}
