<?php declare(strict_types = 1);

namespace PHPStan\Type\Generic;

use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class TemplateTypeFactory
{

	public static function create(TemplateTypeScope $scope, string $name, ?Type $bound): Type
	{
		$strategy = new TemplateTypeParameterStrategy();

		if ($bound instanceof ObjectType) {
			return new TemplateObjectType($scope, $strategy, $name, $bound->getClassName());
		}

		if ($bound === null || $bound instanceof MixedType) {
			return new TemplateMixedType($scope, $strategy, $name);
		}

		return new ErrorType();
	}

}
