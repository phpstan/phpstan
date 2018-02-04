<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'tidy_get_opt_doc',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'obj',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'tidy', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'optname',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
