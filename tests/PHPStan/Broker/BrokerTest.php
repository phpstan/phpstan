<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\Reflection\FunctionReflectionFactory;

class BrokerTest extends \PHPStan\TestCase
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	protected function setUp()
	{
		$this->broker = new Broker(
			[],
			[],
			$this->getMock(FunctionReflectionFactory::class)
		);
	}

	/**
	 * @expectedException PHPStan\Broker\ClassNotFoundException
	 * @expectedExceptionMessage Class NonexistentClass not found.
	 */
	public function testClassNotFound()
	{
		$this->broker->getClass('NonexistentClass');
	}

	/**
	 * @expectedException PHPStan\Broker\FunctionNotFoundException
	 * @expectedExceptionMessage Function nonexistentFunction not found.
	 */
	public function testFunctionNotFound()
	{
		$this->broker->getFunction('nonexistentFunction');
	}

	/**
	 * @expectedException PHPStan\Broker\ClassAutoloadingException
	 * @expectedExceptionMessage ParseError (syntax error, unexpected '{') thrown while autoloading class NonexistentClass.
	 */
	public function testClassAutoloadingException()
	{
		spl_autoload_register(function () {
			require_once __DIR__ . '/../Analyser/data/parse-error.php';
		}, true, true);
		$this->broker->hasClass('NonexistentClass');
	}

}
