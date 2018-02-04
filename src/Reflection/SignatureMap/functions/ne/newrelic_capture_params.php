<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'newrelic_capture_params',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'enable',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\VoidType::__set_state(array())
);
