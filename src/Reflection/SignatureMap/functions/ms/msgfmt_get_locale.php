<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'msgfmt_get_locale',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'formatter',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'messageformatter', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
