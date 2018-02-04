<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'mysqlnd_ms_get_last_gtid',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'connection',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => true, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
