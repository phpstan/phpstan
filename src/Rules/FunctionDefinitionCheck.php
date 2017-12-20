<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorWithPhpDocs;
use PHPStan\Type\NonexistentParentClassType;

class FunctionDefinitionCheck
{

	const VALID_TYPEHINTS = [
		'self',
		'array',
		'callable',
		'string',
		'int',
		'bool',
		'float',
		'void',
		'iterable',
		'object',
		'parent',
	];

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\ClassCaseSensitivityCheck
	 */
	private $classCaseSensitivityCheck;

	/**
	 * @var bool
	 */
	private $checkClassCaseSensitivity;

	/**
	 * @var bool
	 */
	private $checkThisOnly;

	public function __construct(
		Broker $broker,
		ClassCaseSensitivityCheck $classCaseSensitivityCheck,
		bool $checkClassCaseSensitivity,
		bool $checkThisOnly
	)
	{
		$this->broker = $broker;
		$this->classCaseSensitivityCheck = $classCaseSensitivityCheck;
		$this->checkClassCaseSensitivity = $checkClassCaseSensitivity;
		$this->checkThisOnly = $checkThisOnly;
	}

	/**
	 * @param \PhpParser\Node\FunctionLike $function
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param string $parameterMessage
	 * @param string $returnMessage
	 * @return string[]
	 */
	public function checkFunction(
		FunctionLike $function,
		Scope $scope,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		if ($function instanceof ClassMethod) {
			return $this->checkParametersAcceptor(
				$scope->getClassReflection()->getNativeMethod($function->name),
				$parameterMessage,
				$returnMessage
			);
		}
		if ($function instanceof Function_) {
			$functionName = $function->name;
			if (isset($function->namespacedName)) {
				$functionName = (string) $function->namespacedName;
			}
			$functionNameName = new Name($functionName);
			if (!$this->broker->hasFunction($functionNameName)) {
				return [];
			}
			return $this->checkParametersAcceptor(
				$this->broker->getFunction($functionNameName),
				$parameterMessage,
				$returnMessage
			);
		}

		$errors = [];
		foreach ($function->getParams() as $param) {
			$class = $param->type instanceof NullableType
				? (string) $param->type->type
				: (string) $param->type;
			if ($class === '' || in_array($class, self::VALID_TYPEHINTS, true)) {
				continue;
			}

			if (!$this->broker->hasClass($class)) {
				$errors[] = sprintf($parameterMessage, $param->name, $class);
			} elseif ($this->checkClassCaseSensitivity) {
				$errors = array_merge(
					$errors,
					$this->classCaseSensitivityCheck->checkClassNames([$class])
				);
			}
		}

		$returnType = $function->getReturnType() instanceof NullableType
			? (string) $function->getReturnType()->type
			: (string) $function->getReturnType();

		if (
			$returnType !== ''
			&& !in_array($returnType, self::VALID_TYPEHINTS, true)
		) {
			if (!$this->broker->hasClass($returnType)) {
				$errors[] = sprintf($returnMessage, $returnType);
			} elseif ($this->checkClassCaseSensitivity) {
				$errors = array_merge(
					$errors,
					$this->classCaseSensitivityCheck->checkClassNames([$returnType])
				);
			}
		}

		return $errors;
	}

	private function checkParametersAcceptor(
		ParametersAcceptorWithPhpDocs $parametersAcceptor,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		$errors = [];
		foreach ($parametersAcceptor->getParameters() as $parameter) {
			if ($this->checkThisOnly) {
				$referencedClasses = $parameter->getType()->getReferencedClasses();
			} else {
				$referencedClasses = array_merge(
					$parameter->getNativeType()->getReferencedClasses(),
					$parameter->getPhpDocType()->getReferencedClasses()
				);
			}
			foreach ($referencedClasses as $class) {
				if (!$this->broker->hasClass($class)) {
					$errors[] = sprintf($parameterMessage, $parameter->getName(), $class);
				}
			}

			if ($this->checkClassCaseSensitivity) {
				$errors = array_merge(
					$errors,
					$this->classCaseSensitivityCheck->checkClassNames($referencedClasses)
				);
			}
			if ($parameter->getType() instanceof NonexistentParentClassType) {
				$errors[] = sprintf($parameterMessage, $parameter->getName(), $parameter->getType()->describe());
			}
		}

		if ($this->checkThisOnly) {
			$returnTypeReferencedClasses = $parametersAcceptor->getReturnType()->getReferencedClasses();
		} else {
			$returnTypeReferencedClasses = array_merge(
				$parametersAcceptor->getNativeReturnType()->getReferencedClasses(),
				$parametersAcceptor->getPhpDocReturnType()->getReferencedClasses()
			);
		}

		foreach ($returnTypeReferencedClasses as $class) {
			if (!$this->broker->hasClass($class)) {
				$errors[] = sprintf($returnMessage, $class);
			}
		}

		if ($this->checkClassCaseSensitivity) {
			$errors = array_merge(
				$errors,
				$this->classCaseSensitivityCheck->checkClassNames($returnTypeReferencedClasses)
			);
		}
		if ($parametersAcceptor->getReturnType() instanceof NonexistentParentClassType) {
			$errors[] = sprintf($returnMessage, $parametersAcceptor->getReturnType()->describe());
		}

		return $errors;
	}

}
