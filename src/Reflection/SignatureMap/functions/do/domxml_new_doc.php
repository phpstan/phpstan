<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'domxml_new_doc',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'version',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DomDocument', ))
);
