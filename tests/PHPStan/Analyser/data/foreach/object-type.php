<?php

namespace ObjectType;

interface MyKey
{

}

interface MyValue
{

}

interface MyIterator extends \Iterator
{

	public function key(): MyKey;

	public function current(): MyValue;

}

interface MyIteratorAggregate extends \IteratorAggregate
{

	public function getIterator(): MyIterator;

}

function test(MyIterator $iterator, MyIteratorAggregate $iteratorAggregate)
{
	foreach ($iterator as $keyFromIterator => $valueFromIterator) {
		'insideFirstForeach';
	}

	foreach ($iteratorAggregate as $keyFromAggregate => $valueFromAggregate) {
		'insideSecondForeach';
	}
}
