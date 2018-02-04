<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'com_isenum',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'com_module',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'variant', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
