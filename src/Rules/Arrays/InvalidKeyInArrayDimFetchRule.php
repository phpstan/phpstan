<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeUtils;
use PHPStan\Type\VerbosityLevel;

class InvalidKeyInArrayDimFetchRule implements \PHPStan\Rules\Rule
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
		if ($node->dim === null) {
			return [];
		}

		$varType = $scope->getType($node->var);
		if (count(TypeUtils::getArrays($varType)) === 0) {
			return [];
		}

		$dimensionType = $scope->getType($node->dim);
		$isSuperType = AllowedArrayKeysTypes::getType()->isSuperTypeOf($dimensionType);
		if ($isSuperType->no()) {
			return [
				sprintf('Invalid array key type %s.', $dimensionType->describe(VerbosityLevel::typeOnly())),
			];
		} elseif ($this->reportMaybes && $isSuperType->maybe() && !$dimensionType instanceof MixedType) {
			return [
				sprintf('Possibly invalid array key type %s.', $dimensionType->describe(VerbosityLevel::typeOnly())),
			];
		}

		return [];
	}

}
