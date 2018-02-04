<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqli_fetch_lengths',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'result',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'mysqli_result', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, ))
);
