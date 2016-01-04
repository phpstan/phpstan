<?php

namespace PHPStan\Reflection\Php;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareClassReflectionExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ClassReflectionExtension;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;

class PhpClassReflectionExtension implements ClassReflectionExtension, BrokerAwareClassReflectionExtension
{

	/** @var \PHPStan\Reflection\Php\PhpMethodReflectionFactory */
	private $methodReflectionFactory;

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	private $properties = [];

	private $methods = [];

	public function __construct(PhpMethodReflectionFactory $methodReflectionFactory)
	{
		$this->methodReflectionFactory = $methodReflectionFactory;
	}

	public function setBroker(Broker $broker)
	{
		$this->broker = $broker;
	}

	public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
	{
		return $classReflection->getNativeReflection()->hasProperty($propertyName);
	}

	public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
	{
		if (!isset($this->properties[$classReflection->getName()][$propertyName])) {
			$propertyReflection = $classReflection->getNativeReflection()->getProperty($propertyName);
			$this->properties[$classReflection->getName()][$propertyName] = new PhpPropertyReflection(
				$this->broker->getClass($propertyReflection->getDeclaringClass()->getName()),
				$propertyReflection
			);
		}

		return $this->properties[$classReflection->getName()][$propertyName];
	}

	public function hasMethod(ClassReflection $classReflection, string $methodName): bool
	{
		return $classReflection->getNativeReflection()->hasMethod($methodName);
	}

	public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
	{
		if (!isset($this->methods[$classReflection->getName()][$methodName])) {
			$methodReflection = $classReflection->getNativeReflection()->getMethod($methodName);
			$this->methods[$classReflection->getName()][$methodName] = $this->methodReflectionFactory->create(
				$this->broker->getClass($methodReflection->getDeclaringClass()->getName()),
				$methodReflection
			);
		}

		return $this->methods[$classReflection->getName()][$methodName];
	}

}
