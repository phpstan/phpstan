<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\FunctionCallParametersCheck;

class CallToFunctionParametersRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\FunctionCallParametersCheck */
	private $check;

	public function __construct(Broker $broker, FunctionCallParametersCheck $check)
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

		return $this->check->check(
			ParametersAcceptorSelector::selectFromArgs(
				$scope,
				$node->args,
				$function->getVariants()
			),
			$scope,
			$node,
			[
				'Function ' . $function->getName() . ' invoked with %d parameter, %d required.',
				'Function ' . $function->getName() . ' invoked with %d parameters, %d required.',
				'Function ' . $function->getName() . ' invoked with %d parameter, at least %d required.',
				'Function ' . $function->getName() . ' invoked with %d parameters, at least %d required.',
				'Function ' . $function->getName() . ' invoked with %d parameter, %d-%d required.',
				'Function ' . $function->getName() . ' invoked with %d parameters, %d-%d required.',
				'Parameter #%d %s of function ' . $function->getName() . ' expects %s, %s given.',
				'Result of function ' . $function->getName() . ' (void) is used.',
				'Parameter #%d %s of function ' . $function->getName() . ' is passed by reference, so it expects variables only.',
			]
		);
	}

}
