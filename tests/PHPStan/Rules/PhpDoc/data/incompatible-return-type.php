<?php

class RootLevel
{
	/**
	 * DocType simple type check with first level inheritance
	 *
	 * @return int
	 */
	public function invalidFirstLevelChildSimpleType()
	{
	}

	/**
	 * DocType compound type check over multi level inheritance
	 *
	 * @return float
	 */
	public function invalidMultiLevelChildCompoundType()
	{
	}

	/**
	 * DocType simple type check with second level inheritance (first level does not override method)
	 *
	 * @return int
	 */
	public function invalidSecondLevelSimpleType()
	{
	}

	/**
	 * DocType compound type check with second level inheritance (first level does not override method)
	 *
	 * @return float
	 */
	public function invalidSecondLevelCompoundType()
	{
	}

	/**
	 * Valid over multiple inheritance levels, each adding a new type
	 *
	 * @return float
	 */
	public function valid()
	{
	}
}

class FirstLevel extends RootLevel
{
	/**
	 * @return string
	 */
	public function invalidFirstLevelChildSimpleType()
	{
	}

	/**
	 * @return int|string
	 */
	public function invalidMultiLevelChildCompoundType()
	{
	}

	/**
	 * @return int|float
	 */
	public function valid()
	{
	}
}

class SecondLevel extends FirstLevel
{
	/**
	 * @return bool
	 */
	public function invalidMultiLevelChildCompoundType()
	{
	}

	/**
	 * @return string
	 */
	public function invalidSecondLevelSimpleType()
	{
	}

	/**
	 * @return int|string
	 */
	public function invalidSecondLevelCompoundType()
	{
	}

	/**
	 * @return bool|float|int
	 */
	public function valid()
	{
	}
}
