<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ObjectType;

class ImpossibleCheckTypeStaticMethodCallRule implements \PHPStan\Rules\Rule
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
		return \PhpParser\Node\Expr\StaticCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\StaticCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!is_string($node->name)) {
			return [];
		}

		$nodeType = $scope->getType($node);
		if (!$nodeType instanceof ConstantBooleanType) {
			return [];
		}

		if (!$nodeType->getValue()) {
			$method = $this->getMethod($node->class, $node->name, $scope);

			return [sprintf(
				'Call to static method %s::%s() will always evaluate to false.',
				$method->getDeclaringClass()->getDisplayName(),
				$method->getName()
			)];
		} elseif ($this->checkAlwaysTrueCheckTypeFunctionCall) {
			$method = $this->getMethod($node->class, $node->name, $scope);

			return [sprintf(
				'Call to static method %s::%s() will always evaluate to true.',
				$method->getDeclaringClass()->getDisplayName(),
				$method->getName()
			)];
		}

		return [];
	}

	/**
	 * @param Node\Name|Expr $class
	 * @param string $methodName
	 * @param Scope $scope
	 * @return MethodReflection
	 * @throws \PHPStan\ShouldNotHappenException
	 */
	private function getMethod(
		$class,
		string $methodName,
		Scope $scope
	): MethodReflection
	{
		if ($class instanceof Node\Name) {
			$calledOnType = new ObjectType($scope->resolveName($class));
		} else {
			$calledOnType = $scope->getType($class);
		}

		if (!$calledOnType->hasMethod($methodName)) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $calledOnType->getMethod($methodName, $scope);
	}

}
