<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'ldap_control_paged_result_response',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'link_identifier',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'result_identifier',
			false,
			PHPStan\Type\ResourceType::__set_state(array()),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'cookie',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			true,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'estimated',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			true,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
