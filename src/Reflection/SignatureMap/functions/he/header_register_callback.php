<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'header_register_callback',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'callback',
			false,
			PHPStan\Type\CallableType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
