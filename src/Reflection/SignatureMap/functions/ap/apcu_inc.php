<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'apcu_inc',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'key',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'step',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'success',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
