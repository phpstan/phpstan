<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'is_callable',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'var',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'syntax_only',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'callable_name',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
