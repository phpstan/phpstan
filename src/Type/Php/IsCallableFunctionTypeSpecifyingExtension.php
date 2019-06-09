<?php declare(strict_types = 1);

namespace PHPStan\Type\Php;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\CallableType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FunctionTypeSpecifyingExtension;

class IsCallableFunctionTypeSpecifyingExtension implements FunctionTypeSpecifyingExtension, TypeSpecifierAwareExtension
{

	/** @var \PHPStan\Type\Php\MethodExistsTypeSpecifyingExtension */
	private $methodExistsExtension;

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	public function __construct(MethodExistsTypeSpecifyingExtension $methodExistsExtension)
	{
		$this->methodExistsExtension = $methodExistsExtension;
	}

	public function isFunctionSupported(FunctionReflection $functionReflection, FuncCall $node, TypeSpecifierContext $context): bool
	{
		return strtolower($functionReflection->getName()) === 'is_callable'
			&& isset($node->args[0])
			&& !$context->null();
	}

	public function specifyTypes(FunctionReflection $functionReflection, FuncCall $node, Scope $scope, TypeSpecifierContext $context): SpecifiedTypes
	{
		if ($context->null()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$value = $node->args[0]->value;
		$valueType = $scope->getType($value);
		if (
			$value instanceof Array_
			&& count($value->items) === 2
			&& $valueType instanceof ConstantArrayType
			&& !$valueType->isCallable()->no()
		) {
			$functionCall = new FuncCall(new Name('method_exists'), [
				new Arg($value->items[0]->value),
				new Arg($value->items[1]->value),
			]);
			return $this->methodExistsExtension->specifyTypes($functionReflection, $functionCall, $scope, $context);
		}

		return $this->typeSpecifier->create($value, new CallableType(), $context);
	}

	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}

}
