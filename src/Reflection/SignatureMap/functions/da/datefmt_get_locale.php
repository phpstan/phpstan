<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'datefmt_get_locale',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fmt',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'IntlDateFormatter', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'which',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
