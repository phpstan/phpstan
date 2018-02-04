<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'ast\\get_supported_versions',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'exclude_deprecated',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'array<int,int>', ))
);
