<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;

class TemplateTypeHelper
{

	/**
	 * Replaces template types with standin types
	 */
	public static function resolveTemplateTypes(Type $type, TemplateTypeMap $standins): Type
	{
		return $type->map(static function (Type $type) use ($standins): Type {
			if ($type instanceof TemplateType && !$type->isArgument()) {
				$newType = $standins->getType($type->getName());

				if ($newType === null) {
					return new ErrorType();
				}

				return $newType;
			}

			return $type;
		});
	}

	/**
	 * Switches all template types to their argument strategy
	 */
	public static function toArgument(Type $type): Type
	{
		return $type->map(static function (Type $type): Type {
			if ($type instanceof TemplateType) {
				return $type->toArgument();
			}

			return $type;
		});
	}

}
