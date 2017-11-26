<?php declare(strict_types = 1);

namespace DependentPhpDocs;

interface Foo extends \IteratorAggregate
{
	/** @param Foo[]|Foo|\Iterator $pages */
	public function addPages($pages);

	/** non-empty */
	public function getIterator();
}
