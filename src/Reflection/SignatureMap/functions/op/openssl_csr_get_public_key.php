<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'openssl_csr_get_public_key',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'csr',
			false,
			PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\ResourceType::__set_state(array(     )),     1 =>      PHPStan\Type\StringType::__set_state(array(     )),   ), )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'use_shortnames',
			true,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\UnionType::__set_state(array(    'types' =>    array (     0 =>      PHPStan\Type\Constant\ConstantBooleanType::__set_state(array(        'value' => false,     )),     1 =>      PHPStan\Type\ResourceType::__set_state(array(     )),   ), ))
);
