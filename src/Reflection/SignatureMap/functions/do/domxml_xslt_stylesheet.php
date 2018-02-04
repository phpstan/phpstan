<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'domxml_xslt_stylesheet',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'xsl_buf',
			false,
			PHPStan\Type\StringType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DomXsltStylesheet', ))
);
