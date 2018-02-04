<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'intlcal_get_minimal_days_in_first_week',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'cal',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'IntlCalendar', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
