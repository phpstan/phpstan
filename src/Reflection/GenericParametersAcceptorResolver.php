<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Generic\ResolvedFunctionVariant;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\TemplateTypeMap;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class GenericParametersAcceptorResolver
{

	/**
	 * Resolve template types
	 *
	 * @param \PHPStan\Type\Type[] $argTypes Unpacked arguments
	 */
	public static function resolve(array $argTypes, ParametersAcceptor $parametersAcceptor): ParametersAcceptor
	{
		$typeMap = TemplateTypeMap::createEmpty();

		foreach ($parametersAcceptor->getParameters() as $i => $param) {
			if (isset($argTypes[$i])) {
				if ($param->isVariadic()) {
					$argType = new ArrayType(new MixedType(), TypeCombinator::union(...array_slice($argTypes, $i)));
				} else {
					$argType = $argTypes[$i];
				}
			} elseif ($param->getDefaultValue() !== null) {
				$argType = $param->getDefaultValue();
			} else {
				break;
			}

			$paramType = $param->getType();
			$typeMap = $typeMap->union($paramType->inferTemplateTypes($argType));
		}

		return new ResolvedFunctionVariant(
			$parametersAcceptor,
			new TemplateTypeMap(array_merge(
				$parametersAcceptor->getTemplateTypeMap()->map(static function (string $name, Type $type): Type {
					return new ErrorType();
				})->getTypes(),
				$typeMap->getTypes()
			))
		);
	}

}
