<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'set_error_handler',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'error_handler',
			false,
			PHPStan\Type\CallableType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'error_types',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\StringType::__set_state(array(     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), ))
);
