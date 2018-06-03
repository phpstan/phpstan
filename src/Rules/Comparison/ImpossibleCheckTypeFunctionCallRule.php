<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;

class ImpossibleCheckTypeFunctionCallRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\Comparison\ImpossibleCheckTypeHelper */
	private $impossibleCheckTypeHelper;

	/** @var bool */
	private $checkAlwaysTrueCheckTypeFunctionCall;

	public function __construct(
		ImpossibleCheckTypeHelper $impossibleCheckTypeHelper,
		bool $checkAlwaysTrueCheckTypeFunctionCall
	)
	{
		$this->impossibleCheckTypeHelper = $impossibleCheckTypeHelper;
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
		if (strtolower($functionName) === 'is_a') {
			return [];
		}
		$isAlways = $this->impossibleCheckTypeHelper->findSpecifiedType($scope, $node);
		if ($isAlways === null) {
			return [];
		}

		if (!$isAlways) {
			return [sprintf(
				'Call to function %s()%s will always evaluate to false.',
				$functionName,
				$this->impossibleCheckTypeHelper->getArgumentsDescription($scope, $node->args)
			)];
		} elseif ($this->checkAlwaysTrueCheckTypeFunctionCall) {
			return [sprintf(
				'Call to function %s()%s will always evaluate to true.',
				$functionName,
				$this->impossibleCheckTypeHelper->getArgumentsDescription($scope, $node->args)
			)];
		}

		return [];
	}

}
