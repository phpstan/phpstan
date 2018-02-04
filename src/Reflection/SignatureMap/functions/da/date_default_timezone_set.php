<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'date_default_timezone_set',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'timezone_identifier',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
