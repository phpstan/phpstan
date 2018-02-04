<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'timezone_offset_get',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'object',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DateTimeZone', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'datetime',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DateTime', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
