<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'fprintf',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'stream',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
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
			'args',
			true,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\FloatType::__set_state(array(     )),     1 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     2 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			true
		),

	],
	true,
	PHPStan\Type\IntegerType::__set_state(array())
);
