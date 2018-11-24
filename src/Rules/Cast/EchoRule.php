<?php declare(strict_types = 1);

namespace PHPStan\Rules\Cast;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class EchoRule implements Rule
{

	/** @var RuleLevelHelper */
	private $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return Node\Stmt\Echo_::class;
	}

	/**
	 * @param \PhpParser\Node\Stmt\Echo_ $node
	 * @param Scope $scope
	 * @return string[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$messages = [];

		foreach ($node->exprs as $key => $expr) {
			$typeResult = $this->ruleLevelHelper->findTypeToCheck(
				$scope,
				$expr,
				'',
				static function (Type $type): bool {
					return !$type->toString() instanceof ErrorType;
				}
			);

			if ($typeResult->getType() instanceof ErrorType
				|| !$typeResult->getType()->toString() instanceof ErrorType
			) {
				continue;
			}

			$messages[] = sprintf(
				'Parameter #%d (%s) of echo cannot be converted to string.',
				$key + 1,
				$typeResult->getType()->describe(VerbosityLevel::value())
			);
		}
		return $messages;
	}

}
