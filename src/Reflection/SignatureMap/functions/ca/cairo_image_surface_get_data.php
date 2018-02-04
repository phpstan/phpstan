<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_image_surface_get_data',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'surface',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairoimagesurface', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
