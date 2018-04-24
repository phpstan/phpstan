<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

class IterableInForeachRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $reportMaybes;

	public function __construct(bool $reportMaybes)
	{
		$this->reportMaybes = $reportMaybes;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Stmt\Foreach_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Foreach_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		$exprType = $scope->getType($node->expr);
		$isIterable = $exprType->isIterable();
		if ($isIterable->no()) {
			return [
				sprintf(
					'Argument of an invalid type %s supplied for foreach, only iterables are supported.',
					$exprType->describe(VerbosityLevel::typeOnly())
				),
			];
		} elseif (
			$this->reportMaybes
			&& !$isIterable->yes()
			&& !$exprType instanceof MixedType
		) {
			return [
				sprintf(
					'Argument of a possibly invalid type %s supplied for foreach, only iterables are supported.',
					$exprType->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [];
	}

}
