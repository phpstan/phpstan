<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Dummy;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\TrivialParametersAcceptor;

class DummyMethodReflection implements MethodReflection
{

	/** @var string */
	private $name;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function getDeclaringClass(): ClassReflection
	{
		$broker = Broker::getInstance();

		return $broker->getClass(\stdClass::class);
	}

	public function isStatic(): bool
	{
		return false;
	}

	public function isPrivate(): bool
	{
		return false;
	}

	public function isPublic(): bool
	{
		return true;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getPrototype(): ClassMemberReflection
	{
		return $this;
	}

	/**
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getVariants(): array
	{
		return [
			new TrivialParametersAcceptor(),
		];
	}

}
