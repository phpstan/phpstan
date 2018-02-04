<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_pattern_get_surface',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'pattern',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairosurfacepattern', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'CairoSurface', ))
);
