<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class PrintRule implements Rule
{

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return Node\Expr\Print_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Print_ $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->expr,
			'',
			static function (Type $type): bool {
				return !$type->toString() instanceof ErrorType;
			}
		);

		if (!$typeResult->getType() instanceof ErrorType
			&& $typeResult->getType()->toString() instanceof ErrorType
		) {
			return [sprintf(
				'Parameter %s of print cannot be converted to string.',
				$typeResult->getType()->describe(VerbosityLevel::value())
			)];
		}

		return [];
	}

}
