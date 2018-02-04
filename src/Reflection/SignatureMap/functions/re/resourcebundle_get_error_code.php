<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'resourcebundle_get_error_code',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'r',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'resourcebundle', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
