<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'collator_get_locale',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'coll',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'collator', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'type',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
