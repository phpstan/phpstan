<?php

/**
 * @template T
 */
class ReflectionClass
{

	/**
	 * @var class-string<T>
	 */
	public $name;

	/**
	 * @param T|class-string<T> $argument
	 */
	public function __construct($argument) {}

	/**
	 * @return class-string<T>
	 */
	public function getName() : string;

	/**
	 * @param mixed ...$args
	 *
	 * @return T
	 */
	public function newInstance(...$args) {}

	/**
	 * @param array<int, mixed> $args
	 *
	 * @return T
	 */
	public function newInstanceArgs(array $args) {}

	/**
	 * @return T
	 */
	public function newInstanceWithoutConstructor();

}
