<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sscanf',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'str',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'format',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'w_vars',
			true,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\FloatType::__set_state(array(     )),     1 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     2 =>      PHPStan\Type\StringType::__set_state(array(     )),     3 =>      PHPStan\Type\NullType::__set_state(array(     )),   ), )),
			true,
			true
		),

	],
	true,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
