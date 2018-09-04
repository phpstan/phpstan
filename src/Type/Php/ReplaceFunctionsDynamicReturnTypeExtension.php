<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\UnionType;

class ReplaceFunctionsDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	/** @var array<string, int> */
	private $functions = [
		'preg_replace' => ['argumentPosition' => 2, 'returnNull' => true],
		'preg_replace_callback' => ['argumentPosition' => 2, 'returnNull' => true],
		'preg_replace_callback_array' => ['argumentPosition' => 1, 'returnNull' => true],
		'str_replace' => ['argumentPosition' => 2, 'returnNull' => false],
		'str_ireplace' => ['argumentPosition' => 2, 'returnNull' => false],
		'substr_replace' => ['argumentPosition' => 0, 'returnNull' => false],
	];

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return array_key_exists($functionReflection->getName(), $this->functions);
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$type = $this->getPreliminarilyResolvedTypeFromFunctionCall($functionReflection, $functionCall, $scope);

		if ($this->functions[$functionReflection->getName()]['returnNull']) {
			$type = $type instanceof UnionType
				? new UnionType(
					array_merge(
						array_filter($type->getTypes(), function (Type $type) { return !$type instanceof NullType; }),
						[new NullType()]
					)
				)
				: new UnionType([$type, new NullType()]);
		}

		return $type;
	}

	public function getPreliminarilyResolvedTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$argumentPosition = $this->functions[$functionReflection->getName()]['argumentPosition'];
		if (count($functionCall->args) <= $argumentPosition) {
			return ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		}

		$subjectArgumentType = $scope->getType($functionCall->args[$argumentPosition]->value);
		$defaultReturnType = ParametersAcceptorSelector::selectSingle($functionReflection->getVariants())->getReturnType();
		if ($subjectArgumentType instanceof MixedType) {
			return TypeUtils::toBenevolentUnion($defaultReturnType);
		}
		$stringType = new StringType();
		$arrayType = new ArrayType(new MixedType(), new MixedType());

		$isStringSuperType = $stringType->isSuperTypeOf($subjectArgumentType);
		$isArraySuperType = $arrayType->isSuperTypeOf($subjectArgumentType);
		$compareSuperTypes = $isStringSuperType->compareTo($isArraySuperType);
		if ($compareSuperTypes === $isStringSuperType) {
			return $stringType;
		} elseif ($compareSuperTypes === $isArraySuperType) {
			if ($subjectArgumentType instanceof ArrayType) {
				return $subjectArgumentType->generalizeValues();
			}
			return $subjectArgumentType;
		}

		return $defaultReturnType;
	}

}
