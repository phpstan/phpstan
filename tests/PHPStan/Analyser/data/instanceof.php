<?php

namespace InstanceOfNamespace;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;

interface BarInterface
{

}

class Foo
{

	public function someMethod(Expr $foo)
	{
		$bar = $foo;
		$baz = doFoo();
		$intersected = new Foo();

		if ($baz instanceof Foo) {
			// ...
		} else {
			while ($foo instanceof ArrayDimFetch) {
				assert($lorem instanceof Lorem);
				if ($dolor instanceof Dolor && $sit instanceof Sit) {
					if ($static instanceof static) {
						if ($self instanceof self) {
							if ($intersected instanceof BarInterface) {
								if ($this instanceof BarInterface) {
									die;
								}
							}
						}
					}
				}
			}
		}
	}

}
