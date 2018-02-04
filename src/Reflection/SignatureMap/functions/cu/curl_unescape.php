<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'curl_unescape',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'ch',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'str',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
