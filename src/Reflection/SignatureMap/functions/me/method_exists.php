<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'method_exists',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'object',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectWithoutClassType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'method',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
