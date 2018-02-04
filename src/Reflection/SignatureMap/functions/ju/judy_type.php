<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'judy_type',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'array',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'judy', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
