<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'get_defined_functions',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'exclude_disabled',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'array<string,array<string,string>>', ))
);
