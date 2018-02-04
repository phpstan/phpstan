<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'ssdeep_fuzzy_hash',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'to_hash',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
