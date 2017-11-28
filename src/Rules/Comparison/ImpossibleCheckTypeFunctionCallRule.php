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

		$sureTypes = $this->typeSpecifier->specifyTypesInCondition($scope, $node)->getSureTypes();
		if (count($sureTypes) !== 1) {
			return [];
		}

		$sureType = reset($sureTypes);
		$argumentType = $scope->getType($sureType[0]);

		/** @var \PHPStan\Type\Type $resultType */
		$resultType = $sureType[1];

		$isSuperType = $resultType->isSuperTypeOf($argumentType);
		$functionName = (string) $node->name;
		if ($functionName === 'is_a') {
			return [];
		}

		if ($isSuperType->no()) {
			return [sprintf(
				'Call to function %s() will always evaluate to false.',
				$functionName
			)];
		} elseif ($isSuperType->yes() && $this->checkAlwaysTrueCheckTypeFunctionCall) {
			return [sprintf(
				'Call to function %s() will always evaluate to true.',
				$functionName
			)];
		}

		return [];
	}

}
