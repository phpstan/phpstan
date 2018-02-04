<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_ps_surface_get_eps',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'surface',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairopssurface', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
