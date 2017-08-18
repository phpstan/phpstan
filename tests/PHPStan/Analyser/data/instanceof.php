<?php

namespace InstanceOfNamespace;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;

class Foo
{

	public function someMethod(Expr $foo)
	{
		$bar = $foo;
		$baz = doFoo();

		if ($baz instanceof Foo) {
			// ...
		} else {
			while ($foo instanceof ArrayDimFetch) {
				assert($lorem instanceof Lorem);
				if ($dolor instanceof Dolor && $sit instanceof Sit) {
					if ($static instanceof static) {
						if ($self instanceof self) {
							die;
						}
					}
				}
			}
		}
	}

}
