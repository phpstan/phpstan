<?php

namespace Tests\Dependency;

class GrandChild extends Child
{

	/**
	 * @param ParamPhpDocReturnTypehint $param
	 * @return MethodPhpDocReturnTypehint
	 */
	public function doFoo(ParamNativeReturnTypehint $param): MethodNativeReturnTypehint
	{
		[, $a, $b] = [1, 2, 3];
	}

}
