<?php

namespace ExtendsPdoStatementCrash;

class CrashOne extends \PDOStatement
{
	public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
	{
	}
}

class CrashTwo extends \PDOStatement
{

	/**
	 * @param int $mode
	 * @return bool
	 */
	public function setFetchMode($mode, $params = null)
	{
		return true;
	}

}

class CrashThree extends \PDOStatement
{

	/**
	 * @param int $fetch_column
	 * @param int $colno
	 * @return bool
	 */
	public function setFetchMode($fetch_column, $colno = null)
	{
		return true;
	}

}

class CrashFour extends \PDOStatement
{

	/**
	 * @param int $fetch_class
	 * @param string $classname
	 * @param array $ctorargs
	 * @return bool
	 */
	public function setFetchMode($fetch_class, $classname = null, $ctorargs = null)
	{
		return true;
	}

}

class CrashFive extends \PDOStatement
{

	/**
	 * @param int $fetch_class
	 * @param string|object $classname
	 * @param array $ctorargs
	 * @return bool
	 */
	public function setFetchMode($fetch_class, $classname = null, $ctorargs = null)
	{
		return true;
	}

}

class CrashSix extends \PDOStatement
{

	/**
	 * @param int $fetch_into
	 * @param object $object
	 * @return bool
	 */
	public function setFetchMode($fetch_into, $object = null)
	{
		return true;
	}

}
