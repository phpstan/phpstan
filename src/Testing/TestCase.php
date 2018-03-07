<?php declare(strict_types = 1);

namespace PHPStan\Testing;

use Nette\DI\Container;
use PHPStan\Broker\Broker;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\File\FileHelper;
use PHPStan\Parser\Parser;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{

	/** @var Container */
	private static $container;

	/** @var TestingKit */
	protected $testingKit;

	public function getContainer(): Container
	{
		if (self::$container === null) {
			$rootDir = $this->getRootDir();
			$containerFactory = new ContainerFactory($rootDir);
			self::$container = $containerFactory->create($rootDir . '/tmp', [
				$containerFactory->getConfigDirectory() . '/config.level7.neon',
			]);
		}

		return self::$container;
	}

	protected function getRootDir(): string
	{
		return __DIR__ . '/../..';
	}

	protected function setUp(): void
	{
		$this->testingKit = new TestingKit($this->getContainer());
	}

	public function createBroker(
		array $dynamicMethodReturnTypeExtensions = [],
		array $dynamicStaticMethodReturnTypeExtensions = []
	): Broker
	{
		return $this->testingKit->createBroker(
			$dynamicMethodReturnTypeExtensions,
			$dynamicStaticMethodReturnTypeExtensions
		);
	}

	public function getParser(): Parser
	{
		return $this->testingKit->getParser();
	}

	public function getFileHelper(): FileHelper
	{
		return $this->testingKit->getFileHelper();
	}

}
