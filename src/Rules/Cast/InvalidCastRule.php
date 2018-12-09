<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Broker\Broker;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class InvalidCastRule implements \PHPStan\Rules\Rule
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Rules\RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(
		Broker $broker,
		RuleLevelHelper $ruleLevelHelper
	)
	{
		$this->broker = $broker;
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return \PhpParser\Node\Expr\Cast::class;
	}

	/**
	 * @param \PhpParser\Node\Expr\Cast $node
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$castTypeCallback = static function (Type $type) use ($node): ?Type {
			if ($node instanceof \PhpParser\Node\Expr\Cast\Int_) {
				return $type->toInteger();
			}

			if ($node instanceof \PhpParser\Node\Expr\Cast\Bool_) {
				return $type->toBoolean();
			}

			if ($node instanceof \PhpParser\Node\Expr\Cast\Double) {
				return $type->toFloat();
			}

			if ($node instanceof \PhpParser\Node\Expr\Cast\String_) {
				return $type->toString();
			}

			return null;
		};

		$typeResult = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->expr,
			'',
			static function (Type $type) use ($castTypeCallback): bool {
				$castType = $castTypeCallback($type);
				if ($castType === null) {
					return true;
				}

				return !$castType instanceof ErrorType;
			}
		);
		$type = $typeResult->getType();
		if ($type instanceof ErrorType) {
			return [];
		}

		$castType = $castTypeCallback($type);
		if ($castType instanceof ErrorType) {
			$classReflection = $this->broker->getClass(\get_class($node));
			$shortName = \strtolower($classReflection->getNativeReflection()->getShortName());
			if ($shortName === 'double') {
				$shortName = 'float';
			} else {
				$shortName = \substr($shortName, 0, -1);
			}

			return [
				RuleErrorBuilder::message(
					\sprintf(
						'Cannot cast %s to %s.',
						$scope->getType($node->expr)->describe(VerbosityLevel::value()),
						$shortName
					)
				)->build(),
			];
		}

		return [];
	}

}
