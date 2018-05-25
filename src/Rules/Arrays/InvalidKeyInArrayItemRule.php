<?php declare(strict_types = 1);

namespace PHPStan\Rules\Arrays;

use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\VerbosityLevel;

class InvalidKeyInArrayItemRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $reportMaybes;

	public function __construct(bool $reportMaybes)
	{
		$this->reportMaybes = $reportMaybes;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\ArrayItem::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\ArrayItem $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(\PhpParser\Node $node, Scope $scope): array
	{
		if ($node->key === null) {
			return [];
		}

		$dimensionType = $scope->getType($node->key);
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
