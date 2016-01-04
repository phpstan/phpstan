<?php declare(strict_types = 1);

namespace PHPStan\Broker;

use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflectionFactory;
use ReflectionClass;

class Broker
{

	/** @var \PHPStan\Reflection\ClassReflectionExtension[] */
	private $classReflectionExtensions;

	/** @var \PHPStan\Reflection\ClassReflection[] */
	private $classReflections = [];

	/** @var \PHPStan\Reflection\FunctionReflectionFactory */
	private $functionReflectionFactory;

	/** @var \PHPStan\Reflection\FunctionReflection[] */
	private $functionReflections = [];

	public function __construct(
		array $classReflectionExtensions,
		FunctionReflectionFactory $functionReflectionFactory
	)
	{
		$this->classReflectionExtensions = $classReflectionExtensions;
		foreach ($this->classReflectionExtensions as $extension) {
			if ($extension instanceof BrokerAwareClassReflectionExtension) {
				$extension->setBroker($this);
			}
		}

		$this->functionReflectionFactory = $functionReflectionFactory;
	}

	public function getClass(string $className): \PHPStan\Reflection\ClassReflection
	{
		if (!$this->hasClass($className)) {
			throw new \PHPStan\Broker\ClassNotFoundException($className);
		}

		if (!isset($this->classReflections[$className])) {
			$this->classReflections[$className] = new ClassReflection($this, $this->classReflectionExtensions, new ReflectionClass($className));
		}

		return $this->classReflections[$className];
	}

	public function hasClass(string $className): bool
	{
		try {
			return class_exists($className) || interface_exists($className) || trait_exists($className);
		} catch (\Throwable $t) {
			throw new \PHPStan\Broker\ClassAutoloadingException(
				$className,
				$t
			);
		}
	}

	public function getFunction(string $functionName): \PHPStan\Reflection\FunctionReflection
	{
		if (!$this->hasFunction($functionName)) {
			throw new \PHPStan\Broker\FunctionNotFoundException($functionName);
		}

		if (!isset($this->functionReflections[$functionName])) {
			$this->functionReflections[$functionName] = $this->functionReflectionFactory->create(new \ReflectionFunction($functionName));
		}

		return $this->functionReflections[$functionName];
	}

	public function hasFunction(string $functionName): bool
	{
		return function_exists($functionName);
	}

}
