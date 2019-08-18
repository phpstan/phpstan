<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\DeprecatableReflection;
use PHPStan\Reflection\FinalizableReflection;
use PHPStan\Reflection\InternableReflection;
use PHPStan\Reflection\MethodPrototypeReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\BuiltinMethodReflection;

class NativeMethodReflection implements MethodReflection, DeprecatableReflection, InternableReflection, FinalizableReflection
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var BuiltinMethodReflection */
	private $reflection;

	/** @var \PHPStan\Reflection\ParametersAcceptor[] */
	private $variants;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @param BuiltinMethodReflection $reflection
	 * @param \PHPStan\Reflection\ParametersAcceptor[] $variants
	 */
	public function __construct(
		Broker $broker,
		ClassReflection $declaringClass,
		BuiltinMethodReflection $reflection,
		array $variants
	)
	{
		$this->broker = $broker;
		$this->declaringClass = $declaringClass;
		$this->reflection = $reflection;
		$this->variants = $variants;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->declaringClass;
	}

	public function isStatic(): bool
	{
		return $this->reflection->isStatic();
	}

	public function isPrivate(): bool
	{
		return $this->reflection->isPrivate();
	}

	public function isPublic(): bool
	{
		return $this->reflection->isPublic();
	}

	public function getPrototype(): ClassMemberReflection
	{
		try {
			$prototypeMethod = $this->reflection->getPrototype();
			$prototypeDeclaringClass = $this->broker->getClass($prototypeMethod->getDeclaringClass()->getName());

			return new MethodPrototypeReflection(
				$prototypeDeclaringClass,
				$prototypeMethod->isStatic(),
				$prototypeMethod->isPrivate(),
				$prototypeMethod->isPublic(),
				$prototypeMethod->isAbstract()
			);
		} catch (\ReflectionException $e) {
			return $this;
		}
	}

	public function getName(): string
	{
		return $this->reflection->getName();
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array
	{
		return $this->variants;
	}

	public function getDeprecatedDescription(): ?string
	{
		return null;
	}

	public function isDeprecated(): bool
	{
		return $this->reflection->isDeprecated();
	}

	public function isInternal(): bool
	{
		return false;
	}

	public function isFinal(): bool
	{
		return false;
	}

}
