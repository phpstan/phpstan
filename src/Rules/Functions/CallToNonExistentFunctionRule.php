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

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 */
	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!($node->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$name = (string) $node->name;
		if ($this->getFunction($scope, $name) === false) {
			return [sprintf('Function %s does not exist.', $name)];
		}

		return [];
	}

	private function getFunction(Scope $scope, $name)
	{
		if ($scope->getNamespace() !== null) {
			$namespacedName = sprintf('%s\\%s', $scope->getNamespace(), $name);
			if ($this->broker->hasFunction($namespacedName)) {
				return $this->broker->getFunction($namespacedName);
			}
		}

		if ($this->broker->hasFunction($name)) {
			return $this->broker->getFunction($name);
		}

		return false;
	}

}
