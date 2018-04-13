<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\CallableExistsCheck;
use PHPStan\Type\CallableType;

class FunctionCallableArgExistsRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	/**
	 * @var \PHPStan\Rules\CallableExistsCheck
	 */
	private $check;

	public function __construct(
		Broker $broker,
		CallableExistsCheck $check
	)
	{
		$this->broker = $broker;
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!($node->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		if (!$this->broker->hasFunction($node->name, $scope)) {
			return [];
		}

		$function = $this->broker->getFunction($node->name, $scope);
		$parameters = $function->getParameters();

		$errors = [];

		foreach ($node->args as $i => $argument) {
			if (!$parameters[$i]->getType() instanceof CallableType &&
				!$this->isCallableArgOfCoreFunction($function->getName(), $i + 1)) {
				continue;
			}

			$messagePrefix = sprintf('Argument #%d %s of %s should be callable, but passed ', $i + 1, $parameters[$i]->getName(), $node->name);
			$errors = array_merge($errors, $this->check->checkCallableArgument($argument, $scope, $messagePrefix));
		}

		return $errors;
	}

  /**
   * @param string $functionName Function name
   * @param int $argIndex Argument index of function signature (starting on 1)
   * @return bool
   */
	private function isCallableArgOfCoreFunction(string $functionName, int $argIndex): bool
	{
		$callableArgsOfCoreFunctions = [
			['call_user_func', 1],
			['call_user_func_array', 1],
			['forward_static_call', 1],
			['forward_static_call_array', 1],
			['usort', 2],
			['uasort', 2],
			['uksort', 2],
			['register_shutdown_function', 1],
			['register_tick_function', 1],
			['array_reduce', 2],
			['array_intersect_ukey', 4],
			['array_uintersect', 4],
			['array_uintersect_assoc', 4],
			['array_intersect_uassoc', 4],
			['array_uintersect_uassoc', 4],
			['array_diff_ukey', 4],
			['array_udiff', 4],
			['array_udiff_assoc', 4],
			['array_diff_uassoc', 4],
			['array_udiff_uassoc', 4],
			['array_filter', 2],
			['array_map', 1],
			['ob_start', 1],
			['array_walk ', 2],
			['array_walk_recursive ', 2],
		];
		foreach ($callableArgsOfCoreFunctions as $candidate) {
			if ($candidate[0] === $functionName && $candidate[1] === $argIndex) {
				return true;
			}
		}
		return false;
	}

}
