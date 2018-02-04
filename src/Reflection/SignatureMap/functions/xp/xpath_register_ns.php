<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'xpath_register_ns',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'xpath_context',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'xpathcontext', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'prefix',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'uri',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
