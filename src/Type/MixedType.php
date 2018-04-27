<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use PHPStan\Reflection\Dummy\DummyPropertyReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\MaybeIterableTypeTrait;
use PHPStan\Type\Traits\MaybeOffsetAccessibleTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;

class MixedType implements CompoundType
{

	use MaybeCallableTypeTrait;
	use MaybeIterableTypeTrait;
	use MaybeOffsetAccessibleTypeTrait;
	use UndecidedBooleanTypeTrait;

	/** @var bool */
	private $isExplicitMixed;

	public function __construct(bool $isExplicitMixed = false)
	{
		$this->isExplicitMixed = $isExplicitMixed;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type): bool
	{
		return true;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof self) {
			return TrinaryLogic::createYes();
		}

		return TrinaryLogic::createMaybe();
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasProperty(string $propertyName): bool
	{
		return true;
	}

	public function getProperty(string $propertyName, Scope $scope): PropertyReflection
	{
		return new DummyPropertyReflection();
	}

	public function canCallMethods(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasMethod(string $methodName): bool
	{
		return true;
	}

	public function getMethod(string $methodName, Scope $scope): MethodReflection
	{
		return new DummyMethodReflection($methodName);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasConstant(string $constantName): bool
	{
		return false;
	}

	public function getConstant(string $constantName): ClassConstantReflection
	{
		throw new \PHPStan\ShouldNotHappenException();
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function describe(VerbosityLevel $level): string
	{
		return 'mixed';
	}

	public function toNumber(): Type
	{
		return new UnionType([
			$this->toInteger(),
			$this->toFloat(),
		]);
	}

	public function toInteger(): Type
	{
		return new IntegerType();
	}

	public function toFloat(): Type
	{
		return new FloatType();
	}

	public function toString(): Type
	{
		return new StringType();
	}

	public function toArray(): Type
	{
		return new ArrayType(new MixedType(), new MixedType());
	}

	public function isExplicitMixed(): bool
	{
		return $this->isExplicitMixed;
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self($properties['isExplicitMixed']);
	}

}
