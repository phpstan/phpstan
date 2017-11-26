<?php declare(strict_types = 1);

namespace CyclicPhpDocs;

interface Foo extends \IteratorAggregate
{
	/** @return iterable<Foo> | Foo */
	public function getIterator();
}
