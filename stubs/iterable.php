<?php

/**
 * @template TKey
 * @template TValue
 */
interface Traversable
{
}

/**
 * @template TKey
 * @template TValue
 *
 * @extends Traversable<TKey, TValue>
 */
interface IteratorAggregate extends Traversable
{

	/**
	 * @return Iterator<TKey, TValue>
	 */
	public function getIterator();

}

/**
 * @template TKey
 * @template TValue
 *
 * @extends Traversable<TKey, TValue>
 */
interface Iterator extends Traversable
{

	/**
	 * @return TValue
	 */
	public function current();

	/**
	 * @return TKey
	 */
	public function key();

}

/**
 * @template TKey
 * @template TValue
 * @template TSend
 * @template TReturn
 *
 * @implements Iterator<TKey, TValue>
 */
class Generator implements Iterator
{

	/**
	 * @return TValue
	 */
	public function current() {}

	/**
	 * @return TKey
	 */
	public function key() {}

	/**
	 * @return TReturn
	 */
	public function getReturn() {}

	/**
	 * @param TSend $value
	 * @return TValue
	 */
	public function send($value) {}

}
