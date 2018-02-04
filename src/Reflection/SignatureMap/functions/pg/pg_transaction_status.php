<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'pg_transaction_status',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'connection',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
