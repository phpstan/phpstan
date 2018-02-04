<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'iterator_count',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'it',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'Traversable', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
