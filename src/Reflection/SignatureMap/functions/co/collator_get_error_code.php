<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'collator_get_error_code',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'coll',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'collator', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
