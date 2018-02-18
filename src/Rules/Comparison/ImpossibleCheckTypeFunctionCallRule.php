<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Constant\ConstantBooleanType;

class ImpossibleCheckTypeFunctionCallRule implements \PHPStan\Rules\Rule
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
		return \PhpParser\Node\Expr\FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[] errors
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Name) {
			return [];
		}

		$functionName = (string) $node->name;
		$nodeType = $scope->getType($node);
		if (!$nodeType instanceof ConstantBooleanType) {
			return [];
		}

		if (!$nodeType->getValue()) {
			return [sprintf(
				'Call to function %s() will always evaluate to false.',
				$functionName
			)];
		} elseif ($this->checkAlwaysTrueCheckTypeFunctionCall) {
			return [sprintf(
				'Call to function %s() will always evaluate to true.',
				$functionName
			)];
		}

		return [];
	}

}
