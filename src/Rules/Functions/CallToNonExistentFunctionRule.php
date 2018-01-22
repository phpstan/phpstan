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

		$lowercaseFunctionName = strtolower((string) $node->name);

		if (
			strpos($lowercaseFunctionName, 'apache_') === 0
			|| strpos($lowercaseFunctionName, 'fastcgi_') === 0
			|| $lowercaseFunctionName === 'getallheaders'
		) {
			return [];
		}

		if (!$this->broker->hasFunction($node->name, $scope)) {
			return [sprintf('Function %s not found.', (string) $node->name)];
		}

		$function = $this->broker->getFunction($node->name, $scope);
		$name = (string) $node->name;

		$calledFunctionName = $this->broker->resolveFunctionName($node->name, $scope);
		if (
			strtolower($function->getName()) === strtolower($calledFunctionName)
			&& $function->getName() !== $calledFunctionName
		) {
			return [sprintf('Call to function %s() with incorrect case: %s', $function->getName(), $name)];
		}

		return [];
	}

}
