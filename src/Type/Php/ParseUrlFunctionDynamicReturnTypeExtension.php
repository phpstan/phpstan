<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

final class ParseUrlFunctionDynamicReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{

	/** @var array<int,Type> */
	private $componentTypesPairedConstants;

	/** @var array<string,Type> */
	private $componentTypesPairedStrings;

	/** @var Type */
	private $allComponentsTogetherType;

	public function isFunctionSupported(FunctionReflection $functionReflection): bool
	{
		return $functionReflection->getName() === 'parse_url';
	}

	public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): Type
	{
		$defaultReturnType = ParametersAcceptorSelector::selectSingle(
			$functionReflection->getVariants()
		)->getReturnType();

		if (count($functionCall->args) < 1) {
			return $defaultReturnType;
		}

		$this->cacheReturnTypes();

		$urlType = $scope->getType($functionCall->args[0]->value);
		if (count($functionCall->args) > 1) {
			$componentType = $scope->getType($functionCall->args[1]->value);

			if (!$componentType instanceof ConstantType) {
				return $this->createAllComponentsReturnType();
			}

			$componentType = $componentType->toInteger();

			if (!$componentType instanceof ConstantIntegerType) {
				throw new \PHPStan\ShouldNotHappenException();
			}
		} else {
			$componentType = new ConstantIntegerType(-1);
		}

		if ($urlType instanceof ConstantStringType) {
			$result = @parse_url($urlType->getValue(), $componentType->getValue());

			return $scope->getTypeFromValue($result);
		}

		if ($componentType->getValue() === -1) {
			return $this->createAllComponentsReturnType();
		}

		return $this->componentTypesPairedConstants[$componentType->getValue()] ?? new ConstantBooleanType(false);
	}

	private function createAllComponentsReturnType(): Type
	{
		if ($this->allComponentsTogetherType === null) {
			$returnTypes = [
				new ConstantBooleanType(false),
				new ConstantArrayType([], []),
			];

			$builder = ConstantArrayTypeBuilder::createEmpty();

			foreach ($this->componentTypesPairedStrings as $componentName => $componentValueType) {
				$builder->setOffsetValueType(new ConstantStringType($componentName), $componentValueType);
			}

			$returnTypes[] = $builder->getArray();

			$this->allComponentsTogetherType = TypeCombinator::union(...$returnTypes);
		}

		return $this->allComponentsTogetherType;
	}

	private function cacheReturnTypes(): void
	{
		if ($this->componentTypesPairedConstants !== null) {
			return;
		}

		$string = new StringType();
		$integer = new IntegerType();

		$stringOrNull = TypeCombinator::addNull($string);
		$integerOrNull = TypeCombinator::addNull($integer);

		$this->componentTypesPairedConstants = [
			PHP_URL_SCHEME => $stringOrNull,
			PHP_URL_HOST => $stringOrNull,
			PHP_URL_PORT => $integerOrNull,
			PHP_URL_USER => $stringOrNull,
			PHP_URL_PASS => $stringOrNull,
			PHP_URL_PATH => $stringOrNull,
			PHP_URL_QUERY => $stringOrNull,
			PHP_URL_FRAGMENT => $stringOrNull,
		];

		$this->componentTypesPairedStrings = [
			'scheme' => $string,
			'host' => $string,
			'port' => $integer,
			'user' => $string,
			'pass' => $string,
			'path' => $string,
			'query' => $string,
			'fragment' => $string,
		];
	}

}
