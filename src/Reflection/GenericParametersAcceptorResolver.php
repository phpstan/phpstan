<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Reflection\Php\DummyParameter;
use PHPStan\Type\ArrayType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\TemplateTypeHelper;
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

		return new FunctionVariant(
			$parametersAcceptor->getTemplateTypeMap()->map(static function (string $name, Type $type) use ($typeMap): Type {
				return $typeMap->getType($name) ?? new ErrorType();
			}),
			array_map(static function (ParameterReflection $param) use ($typeMap): ParameterReflection {
				return new DummyParameter(
					$param->getName(),
					TemplateTypeHelper::resolveTemplateTypes($param->getType(), $typeMap),
					$param->isOptional(),
					$param->passedByReference(),
					$param->isVariadic(),
					$param->getDefaultValue()
				);
			}, $parametersAcceptor->getParameters()),
			$parametersAcceptor->isVariadic(),
			TemplateTypeHelper::resolveTemplateTypes($parametersAcceptor->getReturnType(), $typeMap)
		);
	}

}
