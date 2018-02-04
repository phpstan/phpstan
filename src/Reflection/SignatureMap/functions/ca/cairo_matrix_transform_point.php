<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_matrix_transform_point',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'matrix',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairomatrix', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'dx',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'dy',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, ))
);
