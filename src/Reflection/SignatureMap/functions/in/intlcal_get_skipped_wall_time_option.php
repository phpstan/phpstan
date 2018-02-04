<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'intlcal_get_skipped_wall_time_option',
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
