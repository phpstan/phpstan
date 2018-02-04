<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'session_cache_limiter',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'new_cache_limiter',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
