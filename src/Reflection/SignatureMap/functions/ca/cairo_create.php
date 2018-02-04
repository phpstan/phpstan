<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_create',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'surface',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairosurface', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'CairoContext', ))
);
