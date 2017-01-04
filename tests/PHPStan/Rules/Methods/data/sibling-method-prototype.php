<?php

namespace SiblingMethodPrototype;

class Base
{

	protected function foo()
	{

	}

}

class Other extends Base
{

	protected function foo()
	{

	}

}

class Child extends Base {

	public function bar()
	{
		$other = new Other();
		$other->foo();
	}

}

function () {

	new class extends Base {

		public function bar()
		{
			$other = new Other();
			$other->foo();
		}

	};

};
