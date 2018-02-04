<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'tidy_clean_repair',
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
	PHPStan\Type\BooleanType::__set_state(array())
);
