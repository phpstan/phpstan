<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'datefmt_is_lenient',
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
	PHPStan\Type\BooleanType::__set_state(array())
);
