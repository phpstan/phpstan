<?php

class FooCallToParentConstructor
{

	public function __construct()
	{

	}

}

class BarCallToParentConstructor extends FooCallToParentConstructor
{

	public function __construct()
	{
		parent::__construct();
	}

}

class LoremCallToParentConstructor
{

}

class IpsumCallToParentConstructor extends LoremCallToParentConstructor
{

	public function __construct()
	{
		parent::__construct();
	}

}

class ACallToParentConstructor
{

	public function __construct()
	{

	}

}

class BCallToParentConstructor extends ACallToParentConstructor
{

	public function __construct()
	{

	}

}

class CCallToParentConstructor
{

	public function __construct()
	{
		parent::__construct();
	}

}

class DCallToParentConstructor
{

	public function __construct()
	{

	}

}

class ECallToParentConstructor extends DCallToParentConstructor
{

}

class FCallToParentConstructor extends ECallToParentConstructor
{

	public function __construct()
	{

	}

}

interface FooBarCallToParentConstructor
{

	public function __construct();

}

class NestedCallToParentConstruct extends FooCallToParentConstructor
{

	public function __construct()
	{
		if ($bar) {
			test();
		}
		if ($foo) {
			parent::__construct();
		}
	}

}

class FooSoapClient extends \SoapClient
{

	public function __construct()
	{
		parent::__construct();
	}


}

class BarSoapClient extends \SoapClient
{

	public function __construct()
	{

	}


}

class StaticCallOnAVariable extends FooCallToParentConstructor
{

	public function __construct()
	{
		$thisClass = __CLASS__;
		$thisClass::myMethod();
	}

}

abstract class AbstractClassWithAbstractConstructor
{

	abstract public function __construct();

}

class ClassThatExtendsAbstractClassWithAbstractConstructor extends AbstractClassWithAbstractConstructor
{

	public function __construct()
	{

	}

}

class BarCallToMutedParentConstructor extends FooCallToParentConstructor
{

	public function __construct()
	{
		@parent::__construct();
	}

}

class PrivateConstructor
{

	private function __construct()
	{

	}

}

class ExtendsPrivateConstructor extends PrivateConstructor
{

	public function __construct()
	{
		// cannot call parent
	}

}
