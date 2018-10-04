<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class ThrowTypeRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
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
			static function (Type $type) use ($throwableType): bool {
				return $throwableType->isSuperTypeOf($type)->yes();
			}
		);

		$foundType = $typeResult->getType();
		if ($foundType instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}

		$isSuperType = $throwableType->isSuperTypeOf($foundType);
		if ($isSuperType->yes()) {
			return [];
		}

		return [
			sprintf('Invalid type %s to throw.', $foundType->describe(VerbosityLevel::typeOnly())),
		];
	}

}
