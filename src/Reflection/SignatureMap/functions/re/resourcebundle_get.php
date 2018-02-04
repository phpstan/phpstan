<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'resourcebundle_get',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'r',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'resourcebundle', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'index',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\IntegerType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
