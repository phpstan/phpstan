<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sodium_crypto_sign_ed25519_pk_to_curve25519',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'ed25519pk',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
