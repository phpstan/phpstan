<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'untaint',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'string',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'rw_strings',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			true
		),

	],
	true,
	PHPStan\Type\BooleanType::__set_state(array())
);
