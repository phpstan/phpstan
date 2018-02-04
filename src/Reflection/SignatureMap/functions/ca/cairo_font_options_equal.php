<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_font_options_equal',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'options',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairofontoptions', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'other',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairofontoptions', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
