<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'dbx_fetch_row',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'result_identifier',
			false,
			PHPStan\Type\ObjectWithoutClassType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
