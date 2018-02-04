<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'domxml_xslt_stylesheet_doc',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'xsl_doc',
			false,
			PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DOMDocument', )),
			false,
			false
		),

	],
	false,
	PHPStan\Type\ObjectType::__set_state(array(    'className' => 'DomXsltStylesheet', ))
);
