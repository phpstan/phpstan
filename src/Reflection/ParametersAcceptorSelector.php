<?php declare(strict_types = 1);

namespace PHPStan\Reflection;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\MixedType;
use PHPStan\Type\TypeCombinator;

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

		$types = [];
		$unpack = false;
		foreach ($args as $arg) {
			$type = $scope->getType($arg->value);
			if ($arg->unpack) {
				$unpack = true;
				$types[] = $type->getIterableValueType();
			} else {
				$types[] = $type;
			}
		}

		return self::selectFromTypes($types, $parametersAcceptors, $unpack);
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @param ParametersAcceptor[] $parametersAcceptors
	 * @param bool $unpack
	 * @return ParametersAcceptor
	 */
	public static function selectFromTypes(
		array $types,
		array $parametersAcceptors,
		bool $unpack
	): ParametersAcceptor
	{
		if (count($parametersAcceptors) === 1) {
			return $parametersAcceptors[0];
		}

		$typesCount = count($types);
		$acceptableAcceptors = [];

		foreach ($parametersAcceptors as $parametersAcceptor) {
			if ($unpack) {
				$acceptableAcceptors[] = $parametersAcceptor;
				continue;
			}

			$functionParametersMinCount = 0;
			$functionParametersMaxCount = 0;
			foreach ($parametersAcceptor->getParameters() as $parameter) {
				if (!$parameter->isOptional()) {
					$functionParametersMinCount++;
				}

				$functionParametersMaxCount++;
			}

			if ($typesCount < $functionParametersMinCount) {
				continue;
			}

			if (
				!$parametersAcceptor->isVariadic()
				&& $typesCount > $functionParametersMaxCount
			) {
				continue;
			}

			$acceptableAcceptors[] = $parametersAcceptor;
		}

		if (count($acceptableAcceptors) === 0) {
			return self::combineAcceptors($parametersAcceptors);
		}

		if (count($acceptableAcceptors) === 1) {
			return $acceptableAcceptors[0];
		}

		$winningAcceptors = [];
		$winningCertainty = null;
		foreach ($acceptableAcceptors as $acceptableAcceptor) {
			$isSuperType = TrinaryLogic::createYes();
			foreach ($acceptableAcceptor->getParameters() as $i => $parameter) {
				if (!isset($types[$i])) {
					if (!$unpack || count($types) <= 0) {
						break;
					}

					$type = $types[count($types) - 1];
				} else {
					$type = $types[$i];
				}

				if ($parameter->getType() instanceof MixedType) {
					$isSuperType = $isSuperType->and(TrinaryLogic::createMaybe());
				} else {
					$isSuperType = $isSuperType->and($parameter->getType()->isSuperTypeOf($type));
				}
			}

			if ($isSuperType->no()) {
				continue;
			}

			if ($winningCertainty === null) {
				$winningAcceptors[] = $acceptableAcceptor;
				$winningCertainty = $isSuperType;
			} else {
				$comparison = $winningCertainty->compareTo($isSuperType);
				if ($comparison === $isSuperType) {
					$winningAcceptors = [$acceptableAcceptor];
					$winningCertainty = $isSuperType;
				} elseif ($comparison === null) {
					$winningAcceptors[] = $acceptableAcceptor;
				}
			}
		}

		if (count($winningAcceptors) === 0) {
			return self::combineAcceptors($acceptableAcceptors);
		}

		return self::combineAcceptors($winningAcceptors);
	}

	/**
	 * @param ParametersAcceptor[] $acceptors
	 * @return ParametersAcceptor
	 */
	public static function combineAcceptors(array $acceptors): ParametersAcceptor
	{
		if (count($acceptors) === 1) {
			return $acceptors[0];
		}

		$minimumNumberOfParameters = null;
		foreach ($acceptors as $acceptor) {
			$acceptorParametersMinCount = 0;
			foreach ($acceptor->getParameters() as $parameter) {
				if ($parameter->isOptional()) {
					continue;
				}

				$acceptorParametersMinCount++;
			}

			if ($minimumNumberOfParameters !== null && $minimumNumberOfParameters <= $acceptorParametersMinCount) {
				continue;
			}

			$minimumNumberOfParameters = $acceptorParametersMinCount;
		}

		$parameters = [];
		$isVariadic = false;
		$returnType = null;

		foreach ($acceptors as $acceptor) {
			if ($returnType === null) {
				$returnType = $acceptor->getReturnType();
			} else {
				$returnType = TypeCombinator::union($returnType, $acceptor->getReturnType());
			}
			$isVariadic = $isVariadic || $acceptor->isVariadic();

			foreach ($acceptor->getParameters() as $i => $parameter) {
				if (!isset($parameters[$i])) {
					$parameters[$i] = new NativeParameterReflection(
						$parameter->getName(),
						$i + 1 > $minimumNumberOfParameters,
						$parameter->getType(),
						$parameter->passedByReference(),
						$parameter->isVariadic()
					);
					continue;
				}

				$isVariadic = $parameters[$i]->isVariadic() || $parameter->isVariadic();

				$parameters[$i] = new NativeParameterReflection(
					$parameters[$i]->getName() !== $parameter->getName() ? sprintf('%s|%s', $parameters[$i]->getName(), $parameter->getName()) : $parameter->getName(),
					$i + 1 > $minimumNumberOfParameters,
					TypeCombinator::union($parameters[$i]->getType(), $parameter->getType()),
					$parameters[$i]->passedByReference()->combine($parameter->passedByReference()),
					$isVariadic
				);

				if ($isVariadic) {
					$parameters = array_slice($parameters, 0, $i + 1);
					break;
				}
			}
		}

		/** @var \PHPStan\Type\Type $returnType */
		$returnType = $returnType;

		return new FunctionVariant($parameters, $isVariadic, $returnType);
	}

}
