<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'wddx_serialize_vars',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'var_name',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'vars',
			true,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			true
		),

	],
	true,
	PHPStan\Type\StringType::__set_state(array())
);
