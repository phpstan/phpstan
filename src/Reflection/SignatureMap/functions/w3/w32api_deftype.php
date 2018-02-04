<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'w32api_deftype',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'typename',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'member1_type',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'member1_name',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'...',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			true
		),

	],
	true,
	PHPStan\Type\BooleanType::__set_state(array())
);
