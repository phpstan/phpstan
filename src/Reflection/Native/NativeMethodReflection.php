<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodPrototypeReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\BuiltinMethodReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Type;

class NativeMethodReflection implements MethodReflection
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var BuiltinMethodReflection */
	private $reflection;

	/** @var \PHPStan\Reflection\ParametersAcceptor[] */
	private $variants;

	/** @var TrinaryLogic */
	private $hasSideEffects;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @param BuiltinMethodReflection $reflection
	 * @param \PHPStan\Reflection\ParametersAcceptor[] $variants
	 * @param TrinaryLogic $hasSideEffects
	 */
	public function __construct(
		Broker $broker,
		ClassReflection $declaringClass,
		BuiltinMethodReflection $reflection,
		array $variants,
		TrinaryLogic $hasSideEffects
	)
	{
		$this->broker = $broker;
		$this->declaringClass = $declaringClass;
		$this->reflection = $reflection;
		$this->variants = $variants;
		$this->hasSideEffects = $hasSideEffects;
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

	public function isDeprecated(): TrinaryLogic
	{
		return $this->reflection->isDeprecated();
	}

	public function isInternal(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function isFinal(): TrinaryLogic
	{
		return TrinaryLogic::createNo();
	}

	public function getThrowType(): ?Type
	{
		return null;
	}

	public function hasSideEffects(): TrinaryLogic
	{
		return $this->hasSideEffects;
	}

	/** @return string|false */
	public function getDocComment()
	{
		return $this->reflection->getDocComment();
	}

}
