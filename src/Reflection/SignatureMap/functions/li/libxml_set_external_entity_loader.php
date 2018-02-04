<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'libxml_set_external_entity_loader',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'resolver_function',
			false,
			PHPStan\Type\CallableType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\MixedType::__set_state(array(    'isExplicitMixed' => false, ))
);
