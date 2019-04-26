<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;

class IsSubclassOfFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, TypeSpecifierContext $context): bool
	{
		return strtolower($functionReflection->getName()) === 'is_subclass_of'
			&& count($node->args) >= 2
			&& !$context->null();
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		$objectType = $scope->getType($node->args[0]->value);
		$stringType = new StringType();
		if (
			!$objectType instanceof MixedType
			&& !$objectType instanceof UnionType
			&& !$stringType->isSuperTypeOf($objectType)->no()
		) {
			return new SpecifiedTypes();
		}

		$classType = $scope->getType($node->args[1]->value);
		if ($classType instanceof ConstantStringType && $classType->getValue() !== '') {
			$type = new ObjectType($classType->getValue());
		} elseif ($objectType instanceof UnionType) {
			$type = TypeCombinator::union(...array_filter(
				$objectType->getTypes(),
				static function (Type $type): bool {
					return $type instanceof ObjectWithoutClassType || $type instanceof TypeWithClassName;
				}
			));
		} else {
			$type = new ObjectWithoutClassType();
		}

		$types = $this->typeSpecifier->create($node->args[0]->value, $type, $context);

		if (!$objectType->isSuperTypeOf(new StringType())->no()
			&& (!isset($node->args[2])
				|| $scope->getType($node->args[2]->value)->equals(new ConstantBooleanType(true)))
		) {
			$stringTypes = $this->typeSpecifier->create(
				$node->args[0]->value,
				$objectType instanceof ConstantStringType ? $objectType : $stringType,
				$context
			);
			$types = $types->intersectWith($stringTypes);
		}

		return $types;
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
