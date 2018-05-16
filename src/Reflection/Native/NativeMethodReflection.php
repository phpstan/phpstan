<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Native;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\DeprecatableReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodPrototypeReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;

class NativeMethodReflection implements MethodReflection, DeprecatableReflection
{

	/** @var \PHPStan\Broker\Broker */
	private $broker;

	/** @var \PHPStan\Reflection\ClassReflection */
	private $declaringClass;

	/** @var \ReflectionMethod */
	private $reflection;

	/** @var bool */
	private $isVariadic;

	/** @var \PHPStan\Reflection\Native\NativeParameterReflection[]  */
	private $parameters;

	/** @var \PHPStan\Type\Type */
	private $returnType;

	/**
	 * @param \PHPStan\Broker\Broker $broker
	 * @param \PHPStan\Reflection\ClassReflection $declaringClass
	 * @param \ReflectionMethod $reflection
	 * @param bool $isVariadic
	 * @param \PHPStan\Reflection\Native\NativeParameterReflection[] $parameters
	 * @param \PHPStan\Type\Type $returnType
	 */
	public function __construct(
		Broker $broker,
		ClassReflection $declaringClass,
		\ReflectionMethod $reflection,
		bool $isVariadic,
		array $parameters,
		Type $returnType
	)
	{
		$this->broker = $broker;
		$this->declaringClass = $declaringClass;
		$this->reflection = $reflection;
		$this->isVariadic = $isVariadic;
		$this->parameters = $parameters;
		$this->returnType = $returnType;
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
				$prototypeMethod->isPublic()
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
		return [
			new FunctionVariant(
				$this->parameters,
				$this->isVariadic,
				$this->returnType
			),
		];
	}

	public function isDeprecated(): bool
	{
		return $this->reflection->isDeprecated();
	}

}
