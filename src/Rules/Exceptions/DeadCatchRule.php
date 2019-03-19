<?php declare(strict_types = 1);

namespace PHPStan\Rules\Exceptions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\VerbosityLevel;

class DeadCatchRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Stmt\TryCatch::class;
	}

	/**
	 * @param Node\Stmt\TryCatch $node
	 * @param Scope $scope
	 * @return RuleError[]
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$catchTypes = array_map(static function (Node\Stmt\Catch_ $catch): Type {
			return TypeCombinator::union(...array_map(static function (Node\Name $className): ObjectType {
				return new ObjectType($className->toString());
			}, $catch->types));
		}, $node->catches);
		$catchesCount = count($catchTypes);
		$errors = [];
		for ($i = 0; $i < $catchesCount - 1; $i++) {
			$firstType = $catchTypes[$i];
			for ($j = $i + 1; $j < $catchesCount; $j++) {
				$secondType = $catchTypes[$j];
				if (!$firstType->isSuperTypeOf($secondType)->yes()) {
					continue;
				}

				$errors[] = RuleErrorBuilder::message(sprintf(
					'Dead catch - %s is already caught by %s above.',
					$secondType->describe(VerbosityLevel::typeOnly()),
					$firstType->describe(VerbosityLevel::typeOnly())
				))->line($node->catches[$j]->getLine())->build();
			}
		}

		return $errors;
	}

}
