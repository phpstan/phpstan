<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'atan',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'number',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\FloatType::__set_state(array())
);
