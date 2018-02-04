<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'qdom_tree',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'doc',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'QDomDocument', ))
);
