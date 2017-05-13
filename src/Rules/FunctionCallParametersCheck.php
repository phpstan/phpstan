<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\ArrayType;
use PHPStan\Type\StringType;
use PHPStan\Type\VoidType;
use PHPStan\TypeX\Is;

class FunctionCallParametersCheck
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var bool */
	private $checkArgumentTypes;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper,
		bool $checkArgumentTypes
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->checkArgumentTypes = $checkArgumentTypes;
	}

	/**
	 * @param \PHPStan\Reflection\ParametersAcceptor $function
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $funcCall
	 * @param string[] $messages Eight message templates
	 * @return string[]
	 */
	public function check(ParametersAcceptor $function, Scope $scope, $funcCall, array $messages): array
	{
		if (
			$function instanceof FunctionReflection
			&& in_array($function->getName(), [
				'implode',
				'strtok',
			], true)
		) {
			$functionParametersMinCount = 1;
			$functionParametersMaxCount = 2;
		} elseif (
			$function instanceof MethodReflection
			&& $function->getDeclaringClass()->getName() === 'DatePeriod'
			&& $function->getName() === '__construct'
		) {
			$functionParametersMinCount = 1;
			$functionParametersMaxCount = 4;
		} else {
			$functionParametersMinCount = 0;
			$functionParametersMaxCount = 0;
			foreach ($function->getParameters() as $parameter) {
				if (!$parameter->isOptional()) {
					$functionParametersMinCount++;
				}

				$functionParametersMaxCount++;
			}

			if ($function->isVariadic()) {
				$functionParametersMaxCount = -1;
			}
		}

		$errors = [];
		$invokedParametersCount = count($funcCall->args);
		foreach ($funcCall->args as $arg) {
			if ($arg->unpack) {
				$invokedParametersCount = max($functionParametersMinCount, $functionParametersMaxCount);
				break;
			}
		}

		if ($invokedParametersCount < $functionParametersMinCount || $invokedParametersCount > $functionParametersMaxCount) {
			if ($functionParametersMinCount === $functionParametersMaxCount) {
				$errors[] = sprintf(
					$invokedParametersCount === 1 ? $messages[0] : $messages[1],
					$invokedParametersCount,
					$functionParametersMinCount
				);
			} elseif ($functionParametersMaxCount === -1 && $invokedParametersCount < $functionParametersMinCount) {
				$errors[] = sprintf(
					$invokedParametersCount === 1 ? $messages[2] : $messages[3],
					$invokedParametersCount,
					$functionParametersMinCount
				);
			} elseif ($functionParametersMaxCount !== -1) {
				$errors[] = sprintf(
					$invokedParametersCount === 1 ? $messages[4] : $messages[5],
					$invokedParametersCount,
					$functionParametersMinCount,
					$functionParametersMaxCount
				);
			}
		}

		if (
			Is::type($function->getReturnType(), VoidType::class)
			&& !$scope->isInFirstLevelStatement()
			&& !$funcCall instanceof \PhpParser\Node\Expr\New_
		) {
			$errors[] = $messages[7];
		}

		if (!$this->checkArgumentTypes) {
			return $errors;
		}

		$parameters = $function->getParameters();
		foreach ($funcCall->args as $i => $argument) {
			if (!isset($parameters[$i])) {
				if (!$function->isVariadic() || count($parameters) === 0) {
					break;
				}

				$parameter = $parameters[count($parameters) - 1];
				$parameterType = $parameter->getType();
				if (Is::type($parameterType, ArrayType::class)) {
					if (!$argument->unpack) {
						$parameterType = $parameterType->getItemType();
					}
				} else {
					break;
				}
			} else {
				$parameter = $parameters[$i];
				$parameterType = $parameter->getType();
				if ($parameter->isVariadic()) {
					if (Is::type($parameterType, ArrayType::class) && !$argument->unpack) {
						$parameterType = $parameterType->getItemType();
					}
				} elseif ($argument->unpack) {
					continue;
				}
			}

			$argumentValueType = $scope->getType($argument->value);

			if (
				!$this->ruleLevelHelper->accepts($parameterType, $argumentValueType)
				&& (
					!(Is::type($parameterType, StringType::class))
					|| !(
						$argumentValueType->getClass() !== null
						&& $this->broker->hasClass($argumentValueType->getClass())
						&& $this->broker->getClass($argumentValueType->getClass())->hasMethod('__toString')
					)
				)
			) {
				$errors[] = sprintf(
					$messages[6],
					$i + 1,
					sprintf('%s$%s', $parameter->isVariadic() ? '...' : '', $parameter->getName()),
					$parameterType->describe(),
					$argumentValueType->describe()
				);
			}

			if (
				$parameter->isPassedByReference()
				&& !$argument->value instanceof \PhpParser\Node\Expr\Variable
				&& !$argument->value instanceof \PhpParser\Node\Expr\ArrayDimFetch
			) {
				$errors[] = sprintf(
					$messages[8],
					$i + 1,
					sprintf('%s$%s', $parameter->isVariadic() ? '...' : '', $parameter->getName())
				);
			}
		}

		return $errors;
	}

}
