<?php declare(strict_types = 1);

namespace PHPStan\Reflection\Type;

use PHPStan\Reflection\ClassMemberReflection;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionVariant;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptor;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class IntersectionTypeMethodReflection implements MethodReflection
{

	/** @var string */
	private $methodName;

	/** @var MethodReflection[] */
	private $methods;

	/**
	 * @param string $methodName
	 * @param \PHPStan\Reflection\MethodReflection[] $methods
	 */
	public function __construct(string $methodName, array $methods)
	{
		$this->methodName = $methodName;
		$this->methods = $methods;
	}

	public function getDeclaringClass(): ClassReflection
	{
		return $this->methods[0]->getDeclaringClass();
	}

	public function isStatic(): bool
	{
		foreach ($this->methods as $method) {
			if ($method->isStatic()) {
				return true;
			}
		}

		return false;
	}

	public function isPrivate(): bool
	{
		foreach ($this->methods as $method) {
			if (!$method->isPrivate()) {
				return false;
			}
		}

		return true;
	}

	public function isPublic(): bool
	{
		foreach ($this->methods as $method) {
			if ($method->isPublic()) {
				return true;
			}
		}

		return false;
	}

	public function getName(): string
	{
		return $this->methodName;
	}

	public function getPrototype(): ClassMemberReflection
	{
		return $this;
	}

	public function getVariants(): array
	{
		$variants = $this->methods[0]->getVariants();
		$returnType = TypeCombinator::intersect(...array_map(static function (MethodReflection $method): Type {
			return TypeCombinator::intersect(...array_map(static function (ParametersAcceptor $acceptor): Type {
				return $acceptor->getReturnType();
			}, $method->getVariants()));
		}, $this->methods));

		return array_map(static function (ParametersAcceptor $acceptor) use ($returnType): ParametersAcceptor {
			return new FunctionVariant(
				$acceptor->getParameters(),
				$acceptor->isVariadic(),
				$returnType
			);
		}, $variants);
	}

}
