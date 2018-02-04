<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_matrix_multiply',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'matrix1',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairomatrix', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'matrix2',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairomatrix', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'CairoMatrix', ))
);
