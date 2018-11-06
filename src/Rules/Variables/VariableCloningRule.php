<?php declare(strict_types = 1);

namespace PHPStan\Rules\Variables;

use PhpParser\Node;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class VariableCloningRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return Clone_::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Clone_ $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return (string|\PHPStan\Rules\RuleError)[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->expr,
			'Cloning object of an unknown class %s.',
			static function (Type $type): bool {
				return $type->isCloneable()->yes();
			}
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return $typeResult->getUnknownClassErrors();
		}
		if ($type->isCloneable()->yes()) {
			return [];
		}

		if ($node->expr instanceof Variable && is_string($node->expr->name)) {
			return [
				sprintf(
					'Cannot clone non-object variable $%s of type %s.',
					$node->expr->name,
					$type->describe(VerbosityLevel::typeOnly())
				),
			];
		}

		return [
			sprintf('Cannot clone %s.', $type->describe(VerbosityLevel::typeOnly())),
		];
	}

}
