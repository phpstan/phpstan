<?php

namespace AnonymousTraitClass;

trait TraitWithTypeSpecification
{

	/** @var string */
	private $string;

	public function doFoo(): void
	{
		if (!$this instanceof FooInterface) {
			return;
		}

		$this->string = 'foo';
		$this->nonexistent = 'bar';
	}

}
