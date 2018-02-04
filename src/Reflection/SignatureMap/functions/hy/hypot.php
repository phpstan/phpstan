<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'hypot',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'num1',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'num2',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\FloatType::__set_state(array())
);
