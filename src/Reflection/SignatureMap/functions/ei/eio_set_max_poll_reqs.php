<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'eio_set_max_poll_reqs',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'nreqs',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\VoidType::__set_state(array())
);
