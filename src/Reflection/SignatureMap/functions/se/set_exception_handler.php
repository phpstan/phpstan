<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'set_exception_handler',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'exception_handler',
			false,
			PHPStan\Type\CallableType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\CallableType::__set_state(array())
);
