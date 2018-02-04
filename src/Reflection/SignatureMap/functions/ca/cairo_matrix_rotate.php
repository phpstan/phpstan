<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_matrix_rotate',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'matrix',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairomatrix', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'radians',
			false,
			PHPStan\Type\FloatType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
