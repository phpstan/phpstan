<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'bcadd',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'left_operand',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'right_operand',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'scale',
			true,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
