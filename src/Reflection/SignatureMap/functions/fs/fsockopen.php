<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'fsockopen',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'hostname',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'port',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'errno',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'errstr',
			true,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'timeout',
			true,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),     1 =>      PHPStan\Type\ResourceType::__set_state(array(     )),   ), ))
);
