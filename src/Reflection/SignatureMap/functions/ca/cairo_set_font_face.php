<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'cairo_set_font_face',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'fontface',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'cairofontface', )),
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
