<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\Rule;

class CallToFunctionStamentWithoutSideEffectsRule implements Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	public function __construct(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Expression::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Expression $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->expr instanceof Node\Expr\FuncCall) {
			return [];
		}

		$funcCall = $node->expr;
		if (!($funcCall->name instanceof \PhpParser\Node\Name)) {
			return [];
		}

		if (!$this->broker->hasFunction($funcCall->name, $scope)) {
			return [];
		}

		$function = $this->broker->getFunction($funcCall->name, $scope);
		if ($function->hasSideEffects()->no()) {
			return [
				sprintf('Call to function %s() on a separate line has no effect.', $function->getName()),
			];
		}

		return [];
	}

}
