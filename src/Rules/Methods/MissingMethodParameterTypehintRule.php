<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\MissingTypehintCheck;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Stmt\ClassMethod>
 */
final class MissingMethodParameterTypehintRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\MissingTypehintCheck */
	private $missingTypehintCheck;

	public function __construct(MissingTypehintCheck $missingTypehintCheck)
	{
		$this->missingTypehintCheck = $missingTypehintCheck;
	}

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
			foreach ($this->checkMethodParameter($methodReflection, $parameterReflection) as $parameterMessage) {
				$messages[] = $parameterMessage;
			}
		}

		return $messages;
	}

	/**
	 * @param \PHPStan\Reflection\MethodReflection $methodReflection
	 * @param \PHPStan\Reflection\ParameterReflection $parameterReflection
	 * @return string[]
	 */
	private function checkMethodParameter(MethodReflection $methodReflection, ParameterReflection $parameterReflection): array
	{
		$parameterType = $parameterReflection->getType();

		if ($parameterType instanceof MixedType && !$parameterType->isExplicitMixed()) {
			return [sprintf(
				'Method %s::%s() has parameter $%s with no typehint specified.',
				$methodReflection->getDeclaringClass()->getDisplayName(),
				$methodReflection->getName(),
				$parameterReflection->getName()
			)];
		}

		$messages = [];
		foreach ($this->missingTypehintCheck->getIterableTypesWithMissingValueTypehint($parameterType) as $iterableType) {
			$messages[] = sprintf(
				'Method %s::%s() has parameter $%s with no value type specified in iterable type %s.',
				$methodReflection->getDeclaringClass()->getDisplayName(),
				$methodReflection->getName(),
				$parameterReflection->getName(),
				$iterableType->describe(VerbosityLevel::typeOnly())
			);
		}

		return $messages;
	}

}
