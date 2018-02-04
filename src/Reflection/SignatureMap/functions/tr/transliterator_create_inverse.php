<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'transliterator_create_inverse',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'obj',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'Transliterator', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'Transliterator', ))
);
