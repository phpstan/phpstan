<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StaticType;
use PHPStan\Type\StringType;

class IsAFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, TypeSpecifierContext $context): bool
	{
		return strtolower($functionReflection->getName()) === 'is_a'
			&& isset($node->args[0])
			&& isset($node->args[1])
			&& !$context->null();
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		if ($context->null()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$classNameArgExpr = $node->args[1]->value;
		$classNameArgExprType = $scope->getType($classNameArgExpr);
		if (
			$classNameArgExpr instanceof ClassConstFetch
			&& $classNameArgExpr->class instanceof Name
			&& $classNameArgExpr->name instanceof \PhpParser\Node\Identifier
			&& strtolower($classNameArgExpr->name->name) === 'class'
		) {
			$className = $scope->resolveName($classNameArgExpr->class);
			if (strtolower($classNameArgExpr->class->toString()) === 'static') {
				$objectType = new StaticType($className);
			} else {
				$objectType = new ObjectType($className);
			}
			$types = $this->typeSpecifier->create($node->args[0]->value, $objectType, $context);
		} elseif ($classNameArgExprType instanceof ConstantStringType) {
			$objectType = new ObjectType($classNameArgExprType->getValue());
			$types = $this->typeSpecifier->create($node->args[0]->value, $objectType, $context);
		} elseif ($context->true()) {
			$objectType = new ObjectWithoutClassType();
			$types = $this->typeSpecifier->create($node->args[0]->value, $objectType, $context);
		} else {
			$types = new SpecifiedTypes();
		}

		if (isset($node->args[2]) && $context->true()) {
			if (!$scope->getType($node->args[2]->value)->isSuperTypeOf(new ConstantBooleanType(true))->no()) {
				$types = $types->intersectWith($this->typeSpecifier->create($node->args[0]->value, new StringType(), $context));
			}
		}

		return $types;
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
