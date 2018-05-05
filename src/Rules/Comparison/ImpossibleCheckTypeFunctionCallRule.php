<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\TypeSpecifier;

class ImpossibleCheckTypeFunctionCallRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Analyser\TypeSpecifier */
	private $typeSpecifier;

	/** @var bool */
	private $checkAlwaysTrueCheckTypeFunctionCall;

	public function __construct(
		TypeSpecifier $typeSpecifier,
		bool $checkAlwaysTrueCheckTypeFunctionCall
	)
	{
		$this->typeSpecifier = $typeSpecifier;
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
		$isAlways = ImpossibleCheckTypeHelper::findSpecifiedType($this->typeSpecifier, $scope, $node);
		if ($isAlways === null) {
			return [];
		}

		if (!$isAlways) {
			return [sprintf(
				'Call to function %s()%s will always evaluate to false.',
				$functionName,
				ImpossibleCheckTypeHelper::getArgumentsDescription($scope, $node->args)
			)];
		} elseif ($this->checkAlwaysTrueCheckTypeFunctionCall) {
			return [sprintf(
				'Call to function %s()%s will always evaluate to true.',
				$functionName,
				ImpossibleCheckTypeHelper::getArgumentsDescription($scope, $node->args)
			)];
		}

		return [];
	}

}
