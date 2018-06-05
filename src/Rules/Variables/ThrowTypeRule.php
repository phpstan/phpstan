<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ThrowTypeRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\RuleLevelHelper */
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
		return \PhpParser\Node\Stmt\Throw_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Throw_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$throwableType = new ObjectType(\Throwable::class);
		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->expr,
			'Throwing object of an unknown class %s.',
			function (Type $type) use ($throwableType): bool {
				return $throwableType->isSuperTypeOf($type)->yes();
			}
		);

		$type = $typeResult->getType();
		if ($type instanceof MixedType && !$type->isExplicitMixed()) {
			return [];
		}

		$isSuperType = $throwableType->isSuperTypeOf($type);

		if ($isSuperType->no()) {
			return [
				sprintf('Invalid type %s to throw.', $type->describe(VerbosityLevel::typeOnly())),
			];
		}

		if ($this->reportMaybes && $isSuperType->maybe()) {
			return [
				sprintf('Possibly invalid type %s to throw.', $type->describe(VerbosityLevel::typeOnly())),
			];
		}

		return [];
	}

}
