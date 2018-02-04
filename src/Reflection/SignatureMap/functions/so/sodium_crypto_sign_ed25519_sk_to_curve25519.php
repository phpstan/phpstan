<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'sodium_crypto_sign_ed25519_sk_to_curve25519',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'ed25519sk',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
