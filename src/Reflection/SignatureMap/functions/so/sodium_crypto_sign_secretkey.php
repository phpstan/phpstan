<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sodium_crypto_sign_secretkey',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'keypair',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
