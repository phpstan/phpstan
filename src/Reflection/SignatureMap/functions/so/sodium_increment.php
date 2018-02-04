<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sodium_increment',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'binary_string',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
