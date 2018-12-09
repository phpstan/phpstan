<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;

class CallableTypeHelper
{

	public static function isParametersAcceptorSuperTypeOf(
		ParametersAcceptor $ours,
		ParametersAcceptor $theirs
	): TrinaryLogic
	{
		$theirParameters = $theirs->getParameters();
		$ourParameters = $ours->getParameters();
		if (\count($theirParameters) > \count($ourParameters)) {
			return TrinaryLogic::createNo();
		}

		$result = null;
		foreach ($theirParameters as $i => $theirParameter) {
			/** @var \PHPStan\Reflection\ParameterReflection $theirParameter */
			/** @var \PHPStan\Reflection\ParameterReflection $ourParameter */
			$ourParameter = $ourParameters[$i];
			$isSuperType = $theirParameter->getType()->isSuperTypeOf($ourParameter->getType());
			if ($result === null) {
				$result = $isSuperType;
			} else {
				/** @var TrinaryLogic $result */
				$result = $result->and($isSuperType);
			}
		}

		$isReturnTypeSuperType = $ours->getReturnType()->isSuperTypeOf($theirs->getReturnType());
		if ($result === null) {
			$result = $isReturnTypeSuperType;
		} else {
			$result = $result->and($isReturnTypeSuperType);
		}

		return $result;
	}

}
