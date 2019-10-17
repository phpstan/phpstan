<?php

namespace InstanceOfNamespace;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use function PHPStan\Analyser\assertType;

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
										assertType('PhpParser\Node\Expr\ArrayDimFetch', $foo);
										assertType('PhpParser\Node\Expr', $bar);
										assertType('*ERROR*', $baz);
										assertType('InstanceOfNamespace\Lorem', $lorem);
										assertType('InstanceOfNamespace\Dolor', $dolor);
										assertType('InstanceOfNamespace\Sit', $sit);
										assertType('InstanceOfNamespace\Foo', $self);
										assertType('static(InstanceOfNamespace\Foo)', $static);
										assertType('static(InstanceOfNamespace\Foo)', clone $static);
										assertType('InstanceOfNamespace\BarInterface&InstanceOfNamespace\Foo', $intersected);
										assertType('$this(InstanceOfNamespace\Foo)&InstanceOfNamespace\BarInterface', $this);
										assertType('InstanceOfNamespace\BarParent', $parent);
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
