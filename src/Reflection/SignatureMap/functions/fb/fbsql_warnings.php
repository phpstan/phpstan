<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'fbsql_warnings',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'onoff',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
