<?php

class FooClassForNodeScopeResolverTestingWithoutNamespace
{

	public function misleadingBoolReturnType(): boolean
	{

	}

	public function misleadingIntReturnType(): integer
	{

	}

}

function () {
	$foo = new FooClassForNodeScopeResolverTestingWithoutNamespace();
	die;
};
