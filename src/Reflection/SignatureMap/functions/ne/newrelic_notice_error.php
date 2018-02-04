<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'newrelic_notice_error',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'message',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'exception',
			true,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'exception', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\VoidType::__set_state(array())
);
