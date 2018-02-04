<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'system',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'command',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'return_value',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
