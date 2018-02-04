<?php declare(strict_types = 1);

return new \PHPStan\Reflection\Native\NativeFunctionReflection(
	'xsl_xsltprocessor_set_security_prefs',
	[
       new \PHPStan\Reflection\Native\NativeParameterReflection(
			'securityprefs',
			false,
			PHPStan\Type\IntegerType::__set_state(array()),
			false,
			false
		),

	],
	false,
	PHPStan\Type\IntegerType::__set_state(array())
);
