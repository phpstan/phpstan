<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;

/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\ClassMethod>
 */
final class MissingMethodReturnTypehintRule implements \PHPStan\Rules\Rule
{

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\ClassMethod::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$scope->isInClass()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		$methodReflection = $scope->getClassReflection()->getNativeMethod($node->name->name);

		$messages = [];

		$message = $this->checkMethodReturnType($methodReflection);
		if ($message !== null) {
			$messages[] = $message;
		}

		return $messages;
	}

	private function checkMethodReturnType(MethodReflection $methodReflection): ?string
	{
		$returnType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();

		if ($returnType instanceof MixedType && !$returnType->isExplicitMixed()) {
			return sprintf(
				'Method %s::%s() has no return typehint specified.',
				$methodReflection->getDeclaringClass()->getDisplayName(),
				$methodReflection->getName()
			);
		}

		return null;
	}

}
