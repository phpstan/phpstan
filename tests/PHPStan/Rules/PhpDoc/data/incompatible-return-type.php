<?php

class Bar
{
	/**
	 * @return int
	 */
	public function invalid1()
	{
	}

	/**
	 * @return float
	 */
	public function invalid2()
	{
	}

	/**
	 * @return float
	 */
	public function valid()
	{
	}
}

class Foo extends Bar
{
	/**
	 * @return string
	 */
	public function invalid1()
	{
	}

	/**
	 * @return string|int
	 */
	public function invalid2()
	{
	}

	/**
	 * @return float|int|string|bool
	 */
	public function valid()
	{
	}
}

class Invalid extends Foo
{
	/**
	 * @return bool
	 */
	public function invalid2()
	{

	}
}
