<?php declare(strict_types = 1);

namespace PHPStan\Rules\Comparison;

use PhpParser\Node\Arg;
use PHPStan\Analyser\Scope;

class ImpossibleCheckTypeHelper
{

	/**
	 * @param Scope $scope
	 * @param \PhpParser\Node\Arg[] $args
	 * @return string
	 */
	public static function getArgumentsDescription(
		Scope $scope,
		array $args
	): string
	{
		if (count($args) === 0) {
			return '';
		}

		$descriptions = array_map(function (Arg $arg) use ($scope): string {
			return $scope->getType($arg->value)->describe();
		}, $args);

		if (count($descriptions) < 3) {
			return sprintf(' with %s', implode(' and ', $descriptions));
		}

		$lastDescription = array_pop($descriptions);

		return sprintf(
			' with arguments %s and %s',
			implode(', ', $descriptions),
			$lastDescription
		);
	}

}
