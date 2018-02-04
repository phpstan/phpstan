<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_scaled_font_create',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fontface',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairofontface', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'matrix',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairomatrix', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'ctm',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairomatrix', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fontoptions',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairofontoptions', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'CairoScaledFont', ))
);
