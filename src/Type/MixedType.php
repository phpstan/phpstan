<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Reflection\ClassMemberAccessAnswerer;
use PHPStan\Reflection\ConstantReflection;
use PHPStan\Reflection\Dummy\DummyConstantReflection;
use PHPStan\Reflection\Dummy\DummyMethodReflection;
use PHPStan\Reflection\Dummy\DummyPropertyReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Traits\MaybeIterableTypeTrait;
use PHPStan\Type\Traits\MaybeOffsetAccessibleTypeTrait;
use PHPStan\Type\Traits\NonGenericTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;

class MixedType implements CompoundType, SubtractableType
{

	use MaybeIterableTypeTrait;
	use MaybeOffsetAccessibleTypeTrait;
	use UndecidedBooleanTypeTrait;
	use NonGenericTypeTrait;

	/** @var bool */
	private $isExplicitMixed;

	/** @var \PHPStan\Type\Type|null */
	private $subtractedType;

	public function __construct(
		bool $isExplicitMixed = false,
		?Type $subtractedType = null
	)
	{
		$this->isExplicitMixed = $isExplicitMixed;
		$this->subtractedType = $subtractedType;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return [];
	}

	public function accepts(Type $type, bool $strictTypes): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($this->subtractedType === null) {
			return TrinaryLogic::createYes();
		}

		if ($type instanceof self) {
			if ($type->subtractedType === null) {
				return TrinaryLogic::createMaybe();
			}
			$isSuperType = $type->subtractedType->isSuperTypeOf($this->subtractedType);
			if ($isSuperType->yes()) {
				return TrinaryLogic::createYes();
			}

			return TrinaryLogic::createMaybe();
		}

		return $this->subtractedType->isSuperTypeOf($type)->negate();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		return new MixedType();
	}

	public function isCallable(): TrinaryLogic
	{
		if (
			$this->subtractedType !== null
			&& $this->subtractedType->isCallable()->yes()
		) {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createMaybe();
	}

	/**
	 * @param \PHPStan\Reflection\ClassMemberAccessAnswerer $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(ClassMemberAccessAnswerer $scope): array
	{
		return [new TrivialParametersAcceptor()];
	}

	public function equals(Type $type): bool
	{
		if (!$type instanceof self) {
			return false;
		}

		if ($this->subtractedType === null) {
			if ($type->subtractedType === null) {
				return true;
			}

			return false;
		}

		if ($type->subtractedType === null) {
			return false;
		}

		return $this->subtractedType->equals($type->subtractedType);
	}

	public function isSubTypeOf(Type $otherType): TrinaryLogic
	{
		if ($otherType instanceof self) {
			return TrinaryLogic::createYes();
		}

		if ($this->subtractedType !== null) {
			$isSuperType = $this->subtractedType->isSuperTypeOf($otherType);
			if ($isSuperType->yes()) {
				return TrinaryLogic::createNo();
			}
		}

		return TrinaryLogic::createMaybe();
	}

	public function isAcceptedBy(Type $acceptingType, bool $strictTypes): TrinaryLogic
	{
		$isSuperType = $this->isSuperTypeOf($acceptingType);
		if ($isSuperType->no()) {
			return $isSuperType;
		}
		return TrinaryLogic::createYes();
	}

	public function canAccessProperties(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasProperty(string $propertyName): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getProperty(string $propertyName, ClassMemberAccessAnswerer $scope): PropertyReflection
	{
		return new DummyPropertyReflection();
	}

	public function canCallMethods(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasMethod(string $methodName): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getMethod(string $methodName, ClassMemberAccessAnswerer $scope): MethodReflection
	{
		return new DummyMethodReflection($methodName);
	}

	public function canAccessConstants(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function hasConstant(string $constantName): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getConstant(string $constantName): ConstantReflection
	{
		return new DummyConstantReflection($constantName);
	}

	public function isCloneable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function describe(VerbosityLevel $level): string
	{
		return $level->handle(
			static function (): string {
				return 'mixed';
			},
			static function (): string {
				return 'mixed';
			},
			function () use ($level): string {
				$description = 'mixed';
				if ($this->subtractedType !== null) {
					$description .= sprintf('~%s', $this->subtractedType->describe($level));
				}

				return $description;
			}
		);
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

	public function subtract(Type $type): Type
	{
		if ($type instanceof self) {
			return new NeverType();
		}
		if ($this->subtractedType !== null) {
			$type = TypeCombinator::union($this->subtractedType, $type);
		}

		return new self($this->isExplicitMixed, $type);
	}

	public function getTypeWithoutSubtractedType(): Type
	{
		return new self($this->isExplicitMixed);
	}

	public function changeSubtractedType(?Type $subtractedType): Type
	{
		return new self($this->isExplicitMixed, $subtractedType);
	}

	public function getSubtractedType(): ?Type
	{
		return $this->subtractedType;
	}

	public function traverse(callable $cb): Type
	{
		return $this;
	}

	public function isArray(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe();
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['isExplicitMixed'],
			$properties['subtractedType'] ?? null
		);
	}

}
