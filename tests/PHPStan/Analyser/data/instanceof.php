<?php

namespace InstanceOfNamespace;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;

interface BarInterface
{

}

abstract class BarParent
{

}

class Foo extends BarParent
{

	public function someMethod(Expr $foo)
	{
		$bar = $foo;
		$baz = doFoo();
		$intersected = new Foo();
		$parent = doFoo();

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
									if ($parent instanceof parent) {
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

}
