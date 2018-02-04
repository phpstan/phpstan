<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'xsl_xsltprocessor_transform_to_uri',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'doc',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DOMDocument', )),
			false,
			false
		),
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'uri',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
