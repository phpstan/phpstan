<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'oci_statement_type',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'stmt',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
