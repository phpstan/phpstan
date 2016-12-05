<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\IterableType;

class FunctionDefinitionCheck
{

	const VALID_TYPEHINTS = [
		'self',
		'static',
		'array',
		'callable',
		'string',
		'int',
		'bool',
		'float',
		'void',
		'iterable',
	];

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
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
		if ($function instanceof ClassMethod && $scope->getClass() !== null) {
			if (!$this->broker->hasClass($scope->getClass())) {
				return [];
			}
			return $this->checkParametersAcceptor(
				$this->broker->getClass($scope->getClass())->getMethod($function->name),
				$parameterMessage,
				$returnMessage
			);
		}
		if ($function instanceof Function_) {
			$functionName = $function->name;
			if (isset($function->namespacedName)) {
				$functionName = $function->namespacedName;
			}
			return $this->checkParametersAcceptor(
				$this->broker->getFunction(new Name($functionName)),
				$parameterMessage,
				$returnMessage
			);
		}

		$errors = [];
		foreach ($function->getParams() as $param) {
			$class = $param->type instanceof NullableType
				? (string) $param->type->type
				: (string) $param->type;
			if (
				$class
				&& !in_array($class, self::VALID_TYPEHINTS, true)
				&& !$this->broker->hasClass($class)
			) {
				$errors[] = sprintf($parameterMessage, $param->name, $class);
			}
		}

		$returnType = $function->getReturnType() instanceof NullableType
			? (string) $function->getReturnType()->type
			: (string) $function->getReturnType();

		if (
			$returnType
			&& !in_array($returnType, self::VALID_TYPEHINTS, true)
			&& !$this->broker->hasClass($returnType)
		) {
			$errors[] = sprintf($returnMessage, $returnType);
		}

		return $errors;
	}

	private function checkParametersAcceptor(
		ParametersAcceptor $parametersAcceptor,
		string $parameterMessage,
		string $returnMessage
	): array
	{
		$errors = [];
		foreach ($parametersAcceptor->getParameters() as $parameter) {
			$type = $parameter->getType();
			if (
				$type->getClass() !== null
				&& !$this->broker->hasClass($type->getClass())
			) {
				$errors[] = sprintf($parameterMessage, $parameter->getName(), $type->getClass());
			} elseif (
				$type instanceof IterableType
			) {
				$nestedItemType = $type->getNestedItemType();
				if (
					$nestedItemType->getItemType()->getClass() !== null
					&& !$this->broker->hasClass($nestedItemType->getItemType()->getClass())
				) {
					$errors[] = sprintf($parameterMessage, $parameter->getName(), $type->describe());
				}
			}
		}

		$returnType = $parametersAcceptor->getReturnType();
		if (
			$returnType->getClass() !== null
			&& !$this->broker->hasClass($returnType->getClass())
		) {
			$errors[] = sprintf($returnMessage, $returnType->getClass());
		} elseif (
			$returnType instanceof IterableType
		) {
			$nestedItemType = $returnType->getNestedItemType();
			if (
				$nestedItemType->getItemType()->getClass() !== null
				&& !$this->broker->hasClass($nestedItemType->getItemType()->getClass())
			) {
				$errors[] = sprintf($returnMessage, $returnType->describe());
			}
		}

		return $errors;
	}

}
