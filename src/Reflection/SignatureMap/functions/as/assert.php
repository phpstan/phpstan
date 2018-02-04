<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'assert',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'assertion',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\BooleanType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'description',
			true,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\StringType::__set_state(array(     )),     1 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'Throwable',     )),     2 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
