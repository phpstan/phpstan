<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;

class CallToNonExistentFunctionRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Broker\Broker
	 */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
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

		list($function, $isNamespaced) = $this->getFunction($scope, $node->name);
		$name = (string) $node->name;
		if ($function === null) {
			return [sprintf('Function %s not found.', $name)];
		}

		$namespacedName = $name;
		if ($scope->getNamespace() !== null && $isNamespaced) {
			$namespacedName = sprintf('%s\\%s', $scope->getNamespace(), $name);
		}

		if ($function->getName() !== $namespacedName) {
			return [sprintf('Call to function %s() with incorrect case: %s', $function->getName(), $name)];
		}

		return [];
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @param \PhpParser\Node\Name $nameNode
	 * @return mixed[]
	 */
	private function getFunction(Scope $scope, \PhpParser\Node\Name $nameNode)
	{
		$name = (string) $nameNode;
		if ($scope->getNamespace() !== null && !$nameNode->isFullyQualified()) {
			$namespacedName = sprintf('%s\\%s', $scope->getNamespace(), $name);
			if ($this->broker->hasFunction($namespacedName)) {
				return [$this->broker->getFunction($namespacedName), true];
			}
		}

		if ($this->broker->hasFunction($name)) {
			return [$this->broker->getFunction($name), false];
		}

		return null;
	}

}
