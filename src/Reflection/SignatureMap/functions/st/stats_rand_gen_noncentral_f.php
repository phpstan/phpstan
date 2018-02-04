<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'stats_rand_gen_noncentral_f',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'dfn',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'dfd',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'xnonc',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\FloatType::__set_state(array())
);
