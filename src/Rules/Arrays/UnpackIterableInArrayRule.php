<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\LiteralArrayNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class UnpackIterableInArrayRule implements Rule
{

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return LiteralArrayNode::class;
	}

	/**
	 * @param \PHPStan\Node\LiteralArrayNode $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Rules\RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];
		foreach ($node->getItemNodes() as $itemNode) {
			$item = $itemNode->getArrayItem();
			if (!$item->unpack) {
				continue;
			}

			$typeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$item->value,
				'',
				static function (Type $type): bool {
					return $type->isIterable()->yes();
				}
			);
			$type = $typeResult->getType();
			if ($type instanceof ErrorType) {
				continue;
			}

			if ($type->isIterable()->yes()) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf(
				'Only iterables can be unpacked, %s given.',
				$type->describe(VerbosityLevel::typeOnly())
			))->line($item->getLine())->build();
		}

		return $errors;
	}

}
