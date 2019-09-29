<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeTraverser;
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
		$classType = $scope->getType($node->args[1]->value);
		$allowStringType = isset($node->args[2]) ? $scope->getType($node->args[2]->value) : new ConstantBooleanType(true);
		$allowString = !$allowStringType->equals(new ConstantBooleanType(false));

		if (!$classType instanceof ConstantStringType) {
			if ($context->truthy()) {
				if ($allowString) {
					$type = TypeCombinator::union(
						new ObjectWithoutClassType(),
						new ClassStringType()
					);
				} else {
					$type = new ObjectWithoutClassType();
				}

				return $this->typeSpecifier->create(
					$node->args[0]->value,
					$type,
					$context
				);
			}

			return new SpecifiedTypes();
		}

		$type = TypeTraverser::map($objectType, static function (Type $type, callable $traverse) use ($classType, $allowString): Type {
			if ($type instanceof UnionType) {
				return $traverse($type);
			}
			if ($type instanceof IntersectionType) {
				return $traverse($type);
			}
			if ($allowString) {
				if ($type instanceof StringType) {
					return new GenericClassStringType(new ObjectType($classType->getValue()));
				}
			}
			if ($type instanceof ObjectWithoutClassType || $type instanceof TypeWithClassName) {
				return new ObjectType($classType->getValue());
			}
			if ($type instanceof MixedType) {
				$objectType = new ObjectType($classType->getValue());
				if ($allowString) {
					return TypeCombinator::union(
						new GenericClassStringType($objectType),
						$objectType
					);
				}

				return $objectType;
			}
			return new NeverType();
		});

		return $this->typeSpecifier->create(
			$node->args[0]->value,
			$type,
			$context
		);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
