<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'xsl_xsltprocessor_transform_to_xml',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'doc',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DOMDocument', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\StringType::__set_state(array())
);
