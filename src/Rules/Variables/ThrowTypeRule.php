<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;

class ThrowTypeRule implements \PHPStan\Rules\Rule
{

	/** @var bool */
	private $reportMaybes;

	/** @var bool */
	private $checkNullables;

	public function __construct(
		bool $reportMaybes,
		bool $checkNullables
	)
	{
		$this->reportMaybes = $reportMaybes;
		$this->checkNullables = $checkNullables;
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
		$type = $scope->getType($node->expr);
		if ($type instanceof MixedType && !$type->isExplicitMixed()) {
			return [];
		}

		if (!$this->checkNullables) {
			$type = TypeCombinator::removeNull($type);
		}

		$throwableType = new ObjectType(\Throwable::class);
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
