<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;

class CallToCountOnlyWithArrayOrCountableRule implements \PHPStan\Rules\Rule
{

	/**
	 * @var \PHPStan\Rules\RuleLevelHelper
	 */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return Node\Expr\FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Name) {
			return [];
		}

		$functionName = strtolower((string) $node->name);
		if ($functionName !== 'count') {
			return [];
		}

		if (!isset($node->args[0])) {
			return [];
		}

		$argumentType = $scope->getType($node->args[0]->value);
		if (
			!$this->ruleLevelHelper->accepts(new ArrayType(new MixedType()), $argumentType)
			&& !$this->ruleLevelHelper->accepts(new ObjectType(\Countable::class), $argumentType)
		) {
			return [
				sprintf(
					'Call to function count() with argument type %s will always result in number 1.',
					$argumentType->describe()
				),
			];
		}

		return [];
	}

}
