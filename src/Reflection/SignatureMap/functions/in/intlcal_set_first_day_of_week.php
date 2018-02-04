<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'intlcal_set_first_day_of_week',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'cal',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'IntlCalendar', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'dayOfWeek',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
