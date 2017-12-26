<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\CallableExistsCheck;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\CallableType;
use PHPStan\Type\ErrorType;

class MethodCallableArgExistsRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Rules\CallableExistsCheck
	 */
	private $check;

	/**
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	public function __construct(
		CallableExistsCheck $check,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->check = $check;
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\MethodCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		$name = $node->name;
		$typeResult = $this->ruleLevelHelper->findTypeToCheck($scope, $node->var, '');
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return [];
		}

		$methodReflection = $type->getMethod($name, $scope);
		$parameters = $methodReflection->getParameters();

		$errors = [];

		foreach ($node->args as $i => $argument) {
			if (!$parameters[$i]->getType() instanceof CallableType) {
				continue;
			}

			$msgPrefix = sprintf('Argument #%d of %s should be callable, but passed ', $i + 1, $node->name);
			$errors = array_merge($errors, $this->check->checkCallableArgument($argument, $scope, $msgPrefix));
		}

		return $errors;
	}

}
