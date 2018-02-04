<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'ini_set',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'varname',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'newvalue',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\BooleanType::__set_state(array(     )),     1 =>      PHPStan\Type\FloatType::__set_state(array(     )),     2 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     3 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), ))
);
