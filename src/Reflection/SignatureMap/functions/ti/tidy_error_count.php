<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'tidy_error_count',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'obj',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'tidy', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
