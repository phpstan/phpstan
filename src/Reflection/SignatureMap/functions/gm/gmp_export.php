<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'gmp_export',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'gmpnumber',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'GMP', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'word_size',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'options',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
