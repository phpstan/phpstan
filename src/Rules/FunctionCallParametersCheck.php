<?php declare(strict_types = 1);

namespace PHPStan\Rules;

use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\ParametersAcceptor;

class FunctionCallParametersCheck
{

	/**
	 * @param \PHPStan\Reflection\ParametersAcceptor $function
	 * @param \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $funcCall
	 * @param string[] $messages Six message templates
	 * @return string[]
	 */
	public function check(ParametersAcceptor $function, $funcCall, array $messages): array
	{
		if ($function instanceof FunctionReflection && $function->getName() === 'implode') {
			$functionParametersMinCount = 1;
			$functionParametersMaxCount = 2;
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

		$invokedParametersCount = count($funcCall->args);
		foreach ($funcCall->args as $arg) {
			if ($arg->unpack) {
				$invokedParametersCount = max($functionParametersMinCount, $functionParametersMaxCount);
				break;
			}
		}

		if ($invokedParametersCount < $functionParametersMinCount || $invokedParametersCount > $functionParametersMaxCount) {
			if ($functionParametersMinCount === $functionParametersMaxCount) {
				return [sprintf(
					$invokedParametersCount === 1 ? $messages[0] : $messages[1],
					$invokedParametersCount,
					$functionParametersMinCount
				)];
			} elseif ($functionParametersMaxCount === -1 && $invokedParametersCount < $functionParametersMinCount) {
				return [sprintf(
					$invokedParametersCount === 1 ? $messages[2] : $messages[3],
					$invokedParametersCount,
					$functionParametersMinCount
				)];
			} elseif ($functionParametersMaxCount !== -1) {
				return [sprintf(
					$invokedParametersCount === 1 ? $messages[4] : $messages[5],
					$invokedParametersCount,
					$functionParametersMinCount,
					$functionParametersMaxCount
				)];
			}
		}

		return [];
	}

}
