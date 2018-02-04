<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'stream_select',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'read_streams',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'resource[]', )),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'write_streams',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'resource[]',     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'except_streams',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ObjectType::__set_state(array(        'className' => 'resource[]',     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'tv_sec',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'tv_usec',
			true,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     1 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),     1 =>      PHPStan\Type\IntegerType::__set_state(array(     )),   ), ))
);
