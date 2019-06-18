<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\TrinaryLogic;

class CallableTypeHelper
{

	public static function isParametersAcceptorSuperTypeOf(
		ParametersAcceptor $ours,
		ParametersAcceptor $theirs,
		bool $treatMixedAsAny
	): TrinaryLogic
	{
		$theirParameters = $theirs->getParameters();
		$ourParameters = $ours->getParameters();

		$result = null;
		foreach ($theirParameters as $i => $theirParameter) {
			if (!isset($ourParameters[$i])) {
				if ($theirParameter->isOptional()) {
					continue;
				}

				return TrinaryLogic::createNo();
			}

			$ourParameter = $ourParameters[$i];
			$ourParameterType = $ourParameter->getType();
			if ($treatMixedAsAny && $ourParameterType instanceof MixedType) {
				$isSuperType = TrinaryLogic::createYes();
			} else {
				$isSuperType = $theirParameter->getType()->isSuperTypeOf($ourParameterType);
			}
			if ($result === null) {
				$result = $isSuperType;
			} else {
				$result = $result->and($isSuperType);
			}
		}

		$theirReturnType = $theirs->getReturnType();
		if ($treatMixedAsAny && $theirReturnType instanceof MixedType) {
			$isReturnTypeSuperType = TrinaryLogic::createYes();
		} else {
			$isReturnTypeSuperType = $ours->getReturnType()->isSuperTypeOf($theirReturnType);
		}
		if ($result === null) {
			$result = $isReturnTypeSuperType;
		} else {
			$result = $result->and($isReturnTypeSuperType);
		}

		return $result;
	}

}
