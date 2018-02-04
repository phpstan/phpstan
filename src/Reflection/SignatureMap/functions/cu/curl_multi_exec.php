<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'curl_multi_exec',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'mh',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'still_running',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
