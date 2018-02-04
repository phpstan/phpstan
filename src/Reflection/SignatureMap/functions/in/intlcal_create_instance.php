<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'intlcal_create_instance',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'timeZone',
			true,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => true, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'locale',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'IntlCalendar', ))
);
