<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Node;
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
	 * @param \PHPStan\Analyser\Node $node
	 * @return string[]
	 */
	public function processNode(Node $node): array
	{
		$functionCallNode = $node->getParserNode();
		if (!($functionCallNode->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		$name = (string) $functionCallNode->name;
		if ($this->getFunction($node, $name) === false) {
			return [sprintf('Function %s does not exist.', $name)];
		}

		return [];
	}

	private function getFunction(Node $node, $name)
	{
		if ($node->getScope()->getNamespace() !== null) {
			$namespacedName = sprintf('%s\\%s', $node->getScope()->getNamespace(), $name);
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
