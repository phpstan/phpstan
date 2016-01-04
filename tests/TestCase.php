<?php declare(strict_types = 1);

namespace PHPStan;

use Nette\DI\Container;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * @var \Nette\DI\Container
	 */
	private static $container;

	/**
	 * @return \Nette\DI\Container
	 */
	public function getContainer()
	{
		return self::$container;
	}

	/**
	 * @param \Nette\DI\Container $container
	 */
	public static function setContainer(Container $container)
	{
		self::$container = $container;
	}

}
