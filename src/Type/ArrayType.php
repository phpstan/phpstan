<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\TrivialParametersAcceptor;
use PHPStan\TrinaryLogic;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\Traits\MaybeCallableTypeTrait;
use PHPStan\Type\Traits\NonObjectTypeTrait;
use PHPStan\Type\Traits\UndecidedBooleanTypeTrait;

class ArrayType implements StaticResolvableType
{

	use MaybeCallableTypeTrait;
	use NonObjectTypeTrait;
	use UndecidedBooleanTypeTrait;

	/** @var \PHPStan\Type\Type */
	private $keyType;

	/** @var \PHPStan\Type\Type */
	private $itemType;

	public function __construct(Type $keyType, Type $itemType)
	{
		$this->keyType = $keyType;
		$this->itemType = $itemType;
	}

	public function getKeyType(): Type
	{
		return $this->keyType;
	}

	public function getItemType(): Type
	{
		return $this->itemType;
	}

	/**
	 * @return string[]
	 */
	public function getReferencedClasses(): array
	{
		return array_merge(
			$this->keyType->getReferencedClasses(),
			$this->getItemType()->getReferencedClasses()
		);
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			return $this->getItemType()->accepts($type->getItemType())
				&& $this->keyType->accepts($type->keyType);
		}

		if ($type instanceof CompoundType) {
			return CompoundTypeHelper::accepts($type, $this);
		}

		return false;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			return $this->getItemType()->isSuperTypeOf($type->getItemType())
				->and($this->keyType->isSuperTypeOf($type->keyType));
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function describe(VerbosityLevel $level): string
	{
		if ($this->keyType instanceof MixedType || $this->keyType instanceof NeverType) {
			if ($this->itemType instanceof MixedType || $this->itemType instanceof NeverType) {
				return 'array';
			}

			return sprintf('array<%s>', $this->itemType->describe($level));
		}

		return sprintf('array<%s, %s>', $this->keyType->describe($level), $this->itemType->describe($level));
	}

	public function generalizeValues(): self
	{
		return new self($this->keyType, TypeUtils::generalizeType($this->itemType));
	}

	public function unionWith(self $otherArray): Type
	{
		return new self(
			TypeCombinator::union($this->getKeyType(), $otherArray->getKeyType()),
			TypeCombinator::union($this->getIterableValueType(), $otherArray->getIterableValueType())
		);
	}

	public function getKeysArray(): self
	{
		return new self(new IntegerType(), $this->keyType);
	}

	public function getValuesArray(): self
	{
		return new self(new IntegerType(), $this->itemType);
	}

	public function resolveStatic(string $className): Type
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->keyType,
				$this->getItemType()->resolveStatic($className)
			);
		}

		return $this;
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->keyType,
				$this->getItemType()->changeBaseClass($className)
			);
		}

		return $this;
	}

	public function isIterable(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getIterableKeyType(): Type
	{
		$keyType = $this->keyType;
		if ($keyType instanceof MixedType) {
			return new UnionType([new IntegerType(), new StringType()]);
		}

		return $keyType;
	}

	public function getIterableValueType(): Type
	{
		return $this->getItemType();
	}

	public function isOffsetAccessible(): TrinaryLogic
	{
		return TrinaryLogic::createYes();
	}

	public function getOffsetValueType(Type $offsetType): Type
	{
		$offsetType = self::castToArrayKeyType($offsetType);
		if ($this->getKeyType()->isSuperTypeOf($offsetType)->no()) {
			return new ErrorType();
		}

		$type = $this->getItemType();
		if ($type instanceof ErrorType) {
			return new MixedType();
		}

		return $type;
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		if ($offsetType === null) {
			$offsetType = new IntegerType();
		}

		return new ArrayType(
			TypeCombinator::union($this->keyType, self::castToArrayKeyType($offsetType)),
			TypeCombinator::union($this->itemType, $valueType)
		);
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe()->and((new StringType())->isSuperTypeOf($this->itemType));
	}

	/**
	 * @param \PHPStan\Analyser\Scope $scope
	 * @return \PHPStan\Reflection\ParametersAcceptor[]
	 */
	public function getCallableParametersAcceptors(Scope $scope): array
	{
		if ($this->isCallable()->no()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return [new TrivialParametersAcceptor()];
	}

	public function toNumber(): Type
	{
		return new ErrorType();
	}

	public function toString(): Type
	{
		return new ErrorType();
	}

	public function toInteger(): Type
	{
		return new ErrorType();
	}

	public function toFloat(): Type
	{
		return new ErrorType();
	}

	public function toArray(): Type
	{
		return $this;
	}

	public function count(): Type
	{
		return new IntegerType();
	}

	public function getFirstValueType(): Type
	{
		return TypeCombinator::union($this->getItemType(), new NullType());
	}

	public function getLastValueType(): Type
	{
		return $this->getFirstValueType();
	}

	public static function castToArrayKeyType(Type $offsetType): Type
	{
		if ($offsetType instanceof UnionType) {
			return TypeCombinator::union(...array_map(function (Type $type): Type {
				return self::castToArrayKeyType($type);
			}, $offsetType->getTypes()));
		}

		if ($offsetType instanceof ConstantScalarType) {
			/** @var int|string $offsetValue */
			$offsetValue = key([$offsetType->getValue() => null]);
			return is_int($offsetValue) ? new ConstantIntegerType($offsetValue) : new ConstantStringType($offsetValue);
		}

		if ($offsetType instanceof IntegerType || $offsetType instanceof FloatType || $offsetType instanceof BooleanType) {
			return new IntegerType();
		}

		if ($offsetType instanceof StringType) {
			return new StringType();
		}

		return new UnionType([new IntegerType(), new StringType()]);
	}

	/**
	 * @param mixed[] $properties
	 * @return Type
	 */
	public static function __set_state(array $properties): Type
	{
		return new self(
			$properties['keyType'],
			$properties['itemType']
		);
	}

}
