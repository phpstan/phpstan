<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use PHPStan\Type\VoidType;

class MethodSignatureRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $reportMaybes;

	public function __construct(bool $reportMaybes)
	{
		$this->reportMaybes = $reportMaybes;
	}

	public function getNodeType(): string
	{
		return ClassMethod::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\ClassMethod $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$methodName = (string) $node->name;

		if ($methodName === '__construct') {
			return [];
		}

		$class = $scope->getClassReflection();
		if ($class === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}
		$method = $class->getMethod($methodName, $scope);
		$parameters = ParametersAcceptorSelector::selectSingle($method->getVariants());

		$errors = [];
		foreach ($this->collectParentMethods($methodName, $class, $scope) as $parentMethod) {
			$parentParameters = ParametersAcceptorSelector::selectSingle($parentMethod->getVariants());

			if ($this->checkParameterCompatibility($parameters, $parentParameters)) {
				continue;
			}

			$errors[] = sprintf(
				'Declaration of %s::%s%s should be compatible with %s::%s%s',
				$class->getName(),
				$methodName,
				$this->parametersToString($parameters),
				$parentMethod->getDeclaringClass()->getName(),
				$methodName,
				$this->parametersToString($parentParameters)
			);
		}

		return $errors;
	}

	/**
	 * @param string $methodName
	 * @param \PHPStan\Reflection\ClassReflection $class
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\MethodReflection[]
	 */
	private function collectParentMethods(string $methodName, ClassReflection $class, Scope $scope): array
	{
		$parentMethods = [];

		$parentClass = $class->getParentClass();
		if ($parentClass !== false && $parentClass->hasMethod($methodName)) {
			$parentMethods[] = $parentClass->getMethod($methodName, $scope);
		}

		foreach ($class->getInterfaces() as $interface) {
			if (!$interface->hasMethod($methodName)) {
				continue;
			}

			$parentMethods[] = $interface->getMethod($methodName, $scope);
		}

		return $parentMethods;
	}

	private function checkParameterCompatibility(
		ParametersAcceptor $parameters,
		ParametersAcceptor $parentParameters
	): bool
	{
		if (!$this->checkReturnTypeCompatibility($parameters->getReturnType(), $parentParameters->getReturnType())) {
			return false;
		}

		if (!$this->checkParameterTypeCompatibility($parameters->getParameters(), $parentParameters->getParameters())) {
			return false;
		}

		return true;
	}

	private function checkReturnTypeCompatibility(
		Type $returnType,
		Type $parentReturnType
	): bool
	{
		// Allow adding `void` return type hints when the parent defines no return type
		if ($returnType instanceof VoidType && $parentReturnType instanceof MixedType) {
			return true;
		}

		$isValid = $parentReturnType->isSuperTypeOf($returnType);
		return $isValid->yes() || (!$this->reportMaybes && $isValid->maybe());
	}

	/**
	 * @param \PHPStan\Reflection\ParameterReflection[] $parameters
	 * @param \PHPStan\Reflection\ParameterReflection[] $parentParameters
	 * @return bool
	 */
	private function checkParameterTypeCompatibility(
		array $parameters,
		array $parentParameters
	): bool
	{
		$numberOfParameters = min(count($parameters), count($parentParameters));

		for ($i = 0; $i < $numberOfParameters; $i++) {
			$parameter = $parameters[$i];
			$parentParameter = $parentParameters[$i];

			$parameterType = $parameter->getType();
			$parentParameterType = $parentParameter->getType();

			$isValid = $parameterType->isSuperTypeOf($parentParameterType);
			if ($isValid->yes() || (!$this->reportMaybes && $isValid->maybe())) {
				continue;
			}

			return false;
		}

		return true;
	}

	private function parametersToString(ParametersAcceptor $parameters): string
	{
		$parametersAsStrings = array_map(static function (ParameterReflection $parameter) {
			return sprintf(
				'%s%s $%s',
				$parameter->isVariadic() ? '...' : '',
				$parameter->getType()->describe(VerbosityLevel::typeOnly()),
				$parameter->getName()
			);
		}, $parameters->getParameters());

		return '(' . implode(', ', $parametersAsStrings) . '): ' . $parameters->getReturnType()->describe(VerbosityLevel::typeOnly());
	}

}
