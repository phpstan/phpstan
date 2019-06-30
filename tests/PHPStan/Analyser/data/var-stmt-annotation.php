<?php

namespace VarStatementAnnotation;

class Foo
{

	/**
	 * @param object $object
	 */
	public function doFoo($object)
	{
		/** @var self $object */
		echo 'fooo';

		die;
	}

	/**
	 * @param object $object
	 */
	public function doBar($object)
	{
		/** @var self $object */
		$object->foo();

		die;
	}

	/**
	 * @param object $object
	 */
	public function doBaz($object)
	{
		/** @var self $object */
		$test = doFoo();

		die;
	}

}
