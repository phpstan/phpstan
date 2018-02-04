<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'oci_internal_debug',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'onoff',
			false,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\VoidType::__set_state(array())
);
