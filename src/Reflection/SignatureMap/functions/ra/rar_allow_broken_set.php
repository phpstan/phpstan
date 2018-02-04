<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'rar_allow_broken_set',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'rarfile',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'RarArchive', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'allow_broken',
			false,
			PHPStan\Type\BooleanType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\BooleanType::__set_state(array())
);
