<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantBooleanType;

class ImpossibleCheckTypeMethodCallRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $checkAlwaysTrueCheckTypeFunctionCall;

	public function __construct(
		bool $checkAlwaysTrueCheckTypeFunctionCall
	)
	{
		$this->checkAlwaysTrueCheckTypeFunctionCall = $checkAlwaysTrueCheckTypeFunctionCall;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\MethodCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\MethodCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name->name)) {
			return [];
		}

		$nodeType = $scope->getType($node);
		if (!$nodeType instanceof ConstantBooleanType) {
			return [];
		}

		if (!$nodeType->getValue()) {
			$method = $this->getMethod($node->var, $node->name->name, $scope);

			return [sprintf(
				'Call to method %s::%s()%s will always evaluate to false.',
				$method->getDeclaringClass()->getDisplayName(),
				$method->getName(),
				ImpossibleCheckTypeHelper::getArgumentsDescription($scope, $node->args)
			)];
		} elseif ($this->checkAlwaysTrueCheckTypeFunctionCall) {
			$method = $this->getMethod($node->var, $node->name->name, $scope);

			return [sprintf(
				'Call to method %s::%s()%s will always evaluate to true.',
				$method->getDeclaringClass()->getDisplayName(),
				$method->getName(),
				ImpossibleCheckTypeHelper::getArgumentsDescription($scope, $node->args)
			)];
		}

		return [];
	}

	private function getMethod(
		Expr $var,
		string $methodName,
		Scope $scope
	): MethodReflection
	{
		$calledOnType = $scope->getType($var);
		if (!$calledOnType->hasMethod($methodName)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $calledOnType->getMethod($methodName, $scope);
	}

}
