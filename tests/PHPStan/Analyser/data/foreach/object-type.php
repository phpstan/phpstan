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

interface MyIteratorAggregateRecursive extends \IteratorAggregate
{

	public function getIterator(): MyIteratorAggregateRecursive;

}

function test(MyIterator $iterator, MyIteratorAggregate $iteratorAggregate, MyIteratorAggregateRecursive $iteratorAggregateRecursive)
{
	foreach ($iterator as $keyFromIterator => $valueFromIterator) {
		'insideFirstForeach';
	}

	foreach ($iteratorAggregate as $keyFromAggregate => $valueFromAggregate) {
		'insideSecondForeach';
	}

	foreach ($iteratorAggregateRecursive as $keyFromRecursiveAggregate => $valueFromRecursiveAggregate) {
		'insideThirdForeach';
	}
}
