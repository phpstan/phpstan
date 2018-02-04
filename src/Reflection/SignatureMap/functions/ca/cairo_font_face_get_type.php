<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_font_face_get_type',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fontface',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairofontface', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
