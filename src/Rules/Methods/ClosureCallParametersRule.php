<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\FunctionCallParametersCheck;
use PHPStan\Type\ClosureType;
use PHPStan\Type\VerbosityLevel;

class ClosureCallParametersRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\FunctionCallParametersCheck */
	private $check;

	public function __construct(FunctionCallParametersCheck $check)
	{
		$this->check = $check;
	}

	public function getNodeType(): string
	{
		return Node\Expr\MethodCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\MethodCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$varType = $scope->getType($node->var);
		if (!($varType instanceof ClosureType)) {
			return [];
		}

		if (!($node->name instanceof Node\Identifier)) {
			return [];
		}

		if ($node->name->name !== 'call') {
			return [];
		}

		$closureDescription = $varType->describe(VerbosityLevel::typeOnly());

		$args = array_slice($node->args, 1);

		return $this->check->check(
			ParametersAcceptorSelector::selectFromArgs(
				$scope,
				$args,
				$varType->getCallableParametersAcceptors($scope)
			),
			$scope,
			new Node\Expr\FuncCall(
				$node->var,
				$args
			),
			[
				$closureDescription . ' call()\'d with %d parameter, %d required.',
				$closureDescription . ' call()\'d with %d parameters, %d required.',
				$closureDescription . ' call()\'d with %d parameter, at least %d required.',
				$closureDescription . ' call()\'d with %d parameters, at least %d required.',
				$closureDescription . ' call()\'d with %d parameter, %d-%d required.',
				$closureDescription . ' call()\'d with %d parameters, %d-%d required.',
				'Parameter #%d %s of ' . $closureDescription . ' expects %s, %s given.',
				'Result of ' . $closureDescription . ' is used.',
				'Parameter #%d %s of ' . $closureDescription . ' is passed by reference, so it expects variables only.',
				'Unable to resolve the template type %s in call() of ' . $closureDescription,
			]
		);
	}

}
