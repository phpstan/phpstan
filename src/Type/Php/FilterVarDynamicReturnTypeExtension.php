<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class FilterVarDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	/** @var array<string, Type> */
	private $filterTypesHashMaps;

	public function __construct()
	{
		$booleanType = new BooleanType();
		$floatOrFalseType = new UnionType([new FloatType(), new ConstantBooleanType(false)]);
		$intOrFalseType = new UnionType([new IntegerType(), new ConstantBooleanType(false)]);
		$stringOrFalseType = new UnionType([new StringType(), new ConstantBooleanType(false)]);

		$this->filterTypesHashMaps = [
			'FILTER_SANITIZE_EMAIL' => $stringOrFalseType,
			'FILTER_SANITIZE_ENCODED' => $stringOrFalseType,
			'FILTER_SANITIZE_MAGIC_QUOTES' => $stringOrFalseType,
			'FILTER_SANITIZE_NUMBER_FLOAT' => $stringOrFalseType,
			'FILTER_SANITIZE_NUMBER_INT' => $stringOrFalseType,
			'FILTER_SANITIZE_SPECIAL_CHARS' => $stringOrFalseType,
			'FILTER_SANITIZE_STRING' => $stringOrFalseType,
			'FILTER_SANITIZE_URL' => $stringOrFalseType,
			'FILTER_VALIDATE_BOOLEAN' => $booleanType,
			'FILTER_VALIDATE_EMAIL' => $stringOrFalseType,
			'FILTER_VALIDATE_FLOAT' => $floatOrFalseType,
			'FILTER_VALIDATE_INT' => $intOrFalseType,
			'FILTER_VALIDATE_IP' => $stringOrFalseType,
			'FILTER_VALIDATE_MAC' => $stringOrFalseType,
			'FILTER_VALIDATE_REGEXP' => $stringOrFalseType,
			'FILTER_VALIDATE_URL' => $stringOrFalseType,
		];
	}

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return strtolower($functionReflection->getName()) === 'filter_var';
	}

	public function getTypeFromFunctionCall(
		FunctionReflection $functionReflection,
		FuncCall $functionCall,
		Scope $scope
	): Type
	{
		$mixedType = new MixedType();

		$filterArg = $functionCall->args[1] ?? null;
		if ($filterArg === null) {
			return $mixedType;
		}

		$filterExpr = $filterArg->value;
		if (!$filterExpr instanceof ConstFetch) {
			return $mixedType;
		}

		$filterName = (string) $filterExpr->name;

		return $this->filterTypesHashMaps[$filterName] ?? $mixedType;
	}

}
