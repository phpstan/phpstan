<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'echo',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'arg1',
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
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
