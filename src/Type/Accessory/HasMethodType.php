<?php declare(strict_types = 1);

namespace PHPStan\Type\Accessory;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\CompoundType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

class HasMethodType extends ObjectWithoutClassType implements AccessoryType, CompoundType
{

	/** @var string */
	private $methodName;

	public function __construct(string $methodName)
	{
		$this->methodName = $methodName;
	}

	private function getCanonicalMethodName(): string
	{
		return strtolower($this->methodName);
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return TrinaryLogic::createFromBoolean($this->equals($type));
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return $type->hasMethod($this->methodName);
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof UnionType || $otherType instanceof IntersectionType) {
			return $otherType->isSuperTypeOf($this);
		}

		if ($otherType instanceof self) {
			$limit = TrinaryLogic::createYes();
		} else {
			$limit = TrinaryLogic::createMaybe();
		}

		return $limit->and($otherType->hasMethod($this->methodName));
	}

	public function equals(Type $type): bool
	{
		return $type instanceof self
			&& $this->getCanonicalMethodName() === $type->getCanonicalMethodName();
	}

	public function describe(\PHPStan\Type\VerbosityLevel $level): string
	{
		return sprintf('hasMethod(%s)', $this->methodName);
	}

	public function hasMethod(string $methodName): TrinaryLogic
	{
		if ($this->getCanonicalMethodName() === strtolower($methodName)) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createMaybe();
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		return new DummyMethodReflection($this->methodName);
	}

	public function isCallable(): TrinaryLogic
	{
		if ($this->getCanonicalMethodName() === '__invoke') {
			return TrinaryLogic::createYes();
		}

		return parent::isCallable();
	}

	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		return [
			new TrivialParametersAcceptor(),
		];
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['methodName']);
	}

}
