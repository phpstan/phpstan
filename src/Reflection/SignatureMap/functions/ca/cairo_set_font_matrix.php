<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_set_font_matrix',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'matrix',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairomatrix', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'context',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairocontext', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
