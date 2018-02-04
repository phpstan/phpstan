<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'date_diff',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'obj1',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DateTimeInterface', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'obj2',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DateTimeInterface', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'absolute',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DateInterval', ))
);
