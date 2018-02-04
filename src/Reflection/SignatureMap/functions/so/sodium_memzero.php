<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sodium_memzero',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'secret',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\VoidType::__set_state(array())
);
