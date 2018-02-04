<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'stream_socket_accept',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'serverstream',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'timeout',
			true,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'peername',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\ResourceType::__set_state(array())
);
