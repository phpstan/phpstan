<?php

namespace TestEventDispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class Foo implements EventDispatcherInterface
{

	public function doFoo(): void
	{
		$this->dispatch(new \stdClass());
	}

}
