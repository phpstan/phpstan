<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'openssl_random_pseudo_bytes',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'length',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'returned_strong_result',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
