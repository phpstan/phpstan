<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sodium_crypto_generichash',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'msg',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'key',
			true,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\StringType::__set_state(array(     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'length',
			true,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
