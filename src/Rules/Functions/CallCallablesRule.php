<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

class CallCallablesRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $reportMaybes;

	public function __construct(bool $reportMaybes)
	{
		$this->reportMaybes = $reportMaybes;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\FuncCall::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\FuncCall $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(
		\PhpParser\Node $node,
		Scope $scope
	): array
	{
		if (!$node->name instanceof \PhpParser\Node\Expr) {
			return [];
		}

		$exprType = $scope->getType($node->name);
		$isCallable = $exprType->isCallable();
		if ($isCallable->no()) {
			return [
				sprintf('Trying to invoke %s but it\'s not a callable.', $exprType->describe(VerbosityLevel::value())),
			];
		} elseif (
			$this->reportMaybes
			&& !$isCallable->yes()
			&& !$exprType instanceof MixedType
		) {
			return [
				sprintf('Trying to invoke %s but it might not be a callable.', $exprType->describe(VerbosityLevel::value())),
			];
		}

		return [];
	}

}
