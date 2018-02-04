<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'xpath_register_ns_auto',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'xpath_context',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'xpathcontext', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'context_node',
			true,
			PHPStan\Type\ObjectWithoutClassType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
