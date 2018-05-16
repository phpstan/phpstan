<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Analyser\Scope;

class ParametersAcceptorSelector
{

	/**
	 * @param ParametersAcceptor[] $parametersAcceptors
	 * @return ParametersAcceptor
	 */
	public static function selectSingle(
		array $parametersAcceptors
	): ParametersAcceptor
	{
		if (count($parametersAcceptors) !== 1) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return $parametersAcceptors[0];
	}

	/**
	 * @param Scope $scope
	 * @param \PhpParser\Node\Arg[] $args
	 * @param ParametersAcceptor[] $parametersAcceptors
	 * @return ParametersAcceptor
	 */
	public static function selectFromArgs(
		Scope $scope,
		array $args,
		array $parametersAcceptors
	): ParametersAcceptor
	{
		if (count($parametersAcceptors) === 1) {
			return $parametersAcceptors[0];
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

}
