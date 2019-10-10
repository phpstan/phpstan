<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;

/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\ClassMethod>
 */
final class MissingMethodParameterTypehintRule implements \PHPStan\Rules\Rule
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

		foreach (ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getParameters() as $parameterReflection) {
			$message = $this->checkMethodParameter($methodReflection, $parameterReflection);
			if ($message === null) {
				continue;
			}

			/** @var string $message */
			$message = $message;

			$messages[] = $message;
		}

		return $messages;
	}

	private function checkMethodParameter(MethodReflection $methodReflection, ParameterReflection $parameterReflection): ?string
	{
		$parameterType = $parameterReflection->getType();

		if ($parameterType instanceof MixedType && !$parameterType->isExplicitMixed()) {
			return sprintf(
				'Method %s::%s() has parameter $%s with no typehint specified.',
				$methodReflection->getDeclaringClass()->getDisplayName(),
				$methodReflection->getName(),
				$parameterReflection->getName()
			);
		}

		return null;
	}

}
