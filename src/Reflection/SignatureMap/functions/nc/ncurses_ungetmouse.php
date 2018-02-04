<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'ncurses_ungetmouse',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'mevent',
			false,
			PHPStan\Type\ArrayType::__set_state(array(    'keyType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemType' =>    PHPStan\Type\MixedType::__set_state(array(      'isExplicitMixed' => false,   )),    'itemTypeInferredFromLiteralArray' => false, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
