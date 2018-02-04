<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'newrelic_get_browser_timing_header',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'include_tags',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
