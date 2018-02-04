<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'oci_get_implicit_resultset',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'statement',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'resource ', ))
);
