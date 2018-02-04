<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'datefmt_get_timetype',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fmt',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'IntlDateFormatter', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
