<?php

namespace AcceptThrowable;

interface SomeInterface
{

}

interface InterfaceExtendingThrowable extends \Throwable
{

}

class NonExceptionClass
{

}

class Foo
{

	public function doFoo(\Throwable $e)
	{

	}

	public function doBar(int $i)
	{

	}

}

function () {
	$foo = new Foo();
	try {

	} catch (SomeInterface $e) {
		$foo->doFoo($e);
		$foo->doBar($e);
	} catch (InterfaceExtendingThrowable $e) {
		$foo->doFoo($e);
		$foo->doBar($e);
	} catch (NonExceptionClass $e) {
		$foo->doFoo($e); // fine, the feasibility must be checked by a different rule
		$foo->doBar($e);
	} catch (\Exception $e) {
		$foo->doFoo($e);
		$foo->doBar($e);
	}
};
