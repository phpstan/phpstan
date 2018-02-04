<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_image_surface_create_from_png',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'file',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'CairoImageSurface', ))
);
