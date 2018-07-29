<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\VerbosityLevel;

class OffsetAccessOnStringRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $reportMaybes;

	public function __construct(bool $reportMaybes)
	{
		$this->reportMaybes = $reportMaybes;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\ArrayDimFetch::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ArrayDimFetch $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		$varType = $scope->getType($node->var);

		// Skip mixed types eventhough they could technically be strings
		if ($varType->equals(new MixedType())) {
			return [];
		}

		$isVarStringType = (new StringType())->isSuperTypeOf($varType);
		if ($isVarStringType->no() || (!$this->reportMaybes && $isVarStringType->maybe())) {
			return [];
		}

		if ($node->dim === null) {
			return ['[] operator not supported for strings'];
		}

		$dimType = $scope->getType($node->dim);
		$isDimIntType = (new IntegerType())->isSuperTypeOf($dimType);
		if ($isDimIntType->yes() || (!$this->reportMaybes && $isDimIntType->maybe())) {
			return [];
		}

		return [sprintf(
			'Illegal %s offset for string',
			$dimType->describe(VerbosityLevel::typeOnly())
		)];
	}

}
