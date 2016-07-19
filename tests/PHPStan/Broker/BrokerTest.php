<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
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
			[],
			$this->createMock(FunctionReflectionFactory::class)
		);
	}

	public function testClassNotFound()
	{
		$this->expectException(\PHPStan\Broker\ClassNotFoundException::class);
		$this->expectExceptionMessage('Class NonexistentClass not found.');
		$this->broker->getClass('NonexistentClass');
	}

	public function testFunctionNotFound()
	{
		$this->expectException(\PHPStan\Broker\FunctionNotFoundException::class);
		$this->expectExceptionMessage('Function nonexistentFunction not found.');

		$scope = $this->createMock(Scope::class);
		$scope->method('getNamespace')
			->willReturn(null);
		$this->broker->getFunction(new Name('nonexistentFunction'), $scope);
	}

	public function testClassAutoloadingException()
	{
		$this->expectException(\PHPStan\Broker\ClassAutoloadingException::class);
		$this->expectExceptionMessage("ParseError (syntax error, unexpected '{') thrown while autoloading class NonexistentClass.");
		spl_autoload_register(function () {
			require_once __DIR__ . '/../Analyser/data/parse-error.php';
		}, true, true);
		$this->broker->hasClass('NonexistentClass');
	}

}
