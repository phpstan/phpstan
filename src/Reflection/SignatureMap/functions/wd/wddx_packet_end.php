<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'wddx_packet_end',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'packet_id',
			false,
			PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
