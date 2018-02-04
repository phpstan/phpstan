<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'libxml_disable_entity_loader',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'disable',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
