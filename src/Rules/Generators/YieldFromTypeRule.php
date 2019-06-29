<?php declare(strict_types = 1);

namespace PHPStan\Rules\Generators;

use PhpParser\Node;
use PhpParser\Node\Expr\YieldFrom;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

class YieldFromTypeRule implements Rule
{

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	/** @var bool */
	private $reportMaybes;

	public function __construct(
		RuleLevelHelper $ruleLevelHelper,
		bool $reportMaybes
	)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
		$this->reportMaybes = $reportMaybes;
	}

	public function getNodeType(): string
	{
		return YieldFrom::class;
	}

	/**
	 * @param YieldFrom $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$exprType = $scope->getType($node->expr);
		$isIterable = $exprType->isIterable();
		$messagePattern = 'Argument of an invalid type %s passed to yield from, only iterables are supported.';
		if ($isIterable->no()) {
			return [
				RuleErrorBuilder::message(sprintf(
					$messagePattern,
					$exprType->describe(VerbosityLevel::typeOnly())
				))->line($node->expr->getLine())->build(),
			];
		} elseif (
			!$exprType instanceof MixedType
			&& $this->reportMaybes
			&& $isIterable->maybe()
		) {
			return [
				RuleErrorBuilder::message(sprintf(
					$messagePattern,
					$exprType->describe(VerbosityLevel::typeOnly())
				))->line($node->expr->getLine())->build(),
			];
		}

		$anonymousFunctionReturnType = $scope->getAnonymousFunctionReturnType();
		$scopeFunction = $scope->getFunction();
		if ($anonymousFunctionReturnType !== null) {
			$returnType = $anonymousFunctionReturnType;
		} elseif ($scopeFunction !== null) {
			$returnType = ParametersAcceptorSelector::selectSingle($scopeFunction->getVariants())->getReturnType();
		} else {
			return []; // already reported by YieldInGeneratorRule
		}

		if ($returnType instanceof MixedType) {
			return [];
		}

		$messages = [];
		if (!$this->ruleLevelHelper->accepts($returnType->getIterableKeyType(), $exprType->getIterableKeyType(), $scope->isDeclareStrictTypes())) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Generator expects key type %s, %s given.',
				$returnType->getIterableKeyType()->describe(VerbosityLevel::typeOnly()),
				$exprType->getIterableKeyType()->describe(VerbosityLevel::typeOnly())
			))->line($node->expr->getLine())->build();
		}
		if (!$this->ruleLevelHelper->accepts($returnType->getIterableValueType(), $exprType->getIterableValueType(), $scope->isDeclareStrictTypes())) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Generator expects value type %s, %s given.',
				$returnType->getIterableValueType()->describe(VerbosityLevel::typeOnly()),
				$exprType->getIterableValueType()->describe(VerbosityLevel::typeOnly())
			))->line($node->expr->getLine())->build();
		}

		return $messages;
	}

}
