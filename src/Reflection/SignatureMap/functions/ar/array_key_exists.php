<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'array_key_exists',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'key',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'search',
			false,
			PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
