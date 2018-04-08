<?php declare(strict_types = 1);

namespace PHPStan\Type;

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

	/** @var bool */
	private $itemTypeInferredFromLiteralArray;

	public function __construct(Type $keyType, Type $itemType, bool $itemTypeInferredFromLiteralArray = false)
	{
		if ($itemType instanceof UnionType && !TypeCombinator::isUnionTypesEnabled()) {
			$itemType = new MixedType();
		}
		$this->keyType = $keyType;
		$this->itemType = $itemType;
		$this->itemTypeInferredFromLiteralArray = $itemTypeInferredFromLiteralArray;
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

	public static function createDeepArrayType(Type $itemType, int $depth, bool $itemTypeInferredFromLiteralArray): self
	{
		for ($i = 0; $i < $depth - 1; $i++) {
			$itemType = new self(new MixedType(), $itemType, false);
		}

		return new self(new MixedType(), $itemType, $itemTypeInferredFromLiteralArray);
	}

	public function isItemTypeInferredFromLiteralArray(): bool
	{
		return $this->itemTypeInferredFromLiteralArray;
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

	public function describe(): string
	{
		if ($this->keyType instanceof MixedType) {
			if ($this->itemType instanceof MixedType) {
				return 'array';
			}

			return sprintf('array<%s>', $this->itemType->describe());
		}

		return sprintf('array<%s, %s>', $this->keyType->describe(), $this->itemType->describe());
	}

	public function resolveStatic(string $className): Type
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->keyType,
				$this->getItemType()->resolveStatic($className),
				$this->isItemTypeInferredFromLiteralArray()
			);
		}

		return $this;
	}

	public function changeBaseClass(string $className): StaticResolvableType
	{
		if ($this->getItemType() instanceof StaticResolvableType) {
			return new self(
				$this->keyType,
				$this->getItemType()->changeBaseClass($className),
				$this->isItemTypeInferredFromLiteralArray()
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
		return $this->keyType;
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
		return $this->getItemType();
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		if ($offsetType === null) {
			$offsetType = new IntegerType();

		} elseif ($offsetType instanceof ConstantType) {
			$offsetType = $offsetType->generalize();
		}

		return new ArrayType(
			TypeCombinator::union($this->keyType, $this->castToArrayKeyType($offsetType)),
			TypeCombinator::union($this->itemType, $valueType),
			$this->itemTypeInferredFromLiteralArray
		);
	}

	public function isCallable(): TrinaryLogic
	{
		return TrinaryLogic::createMaybe()->and((new StringType())->isSuperTypeOf($this->itemType));
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

	protected function castToArrayKeyType(Type $offsetType): Type
	{
		if ($offsetType instanceof ConstantScalarType) {
			/** @var int|string $offsetValue */
			$offsetValue = key([$offsetType->getValue() => null]);
			return is_int($offsetValue) ? new ConstantIntegerType($offsetValue) : new ConstantStringType($offsetValue);

		}

		if ($offsetType instanceof NullType) {
			return new ConstantStringType('');

		}

		if ($offsetType instanceof IntegerType || $offsetType instanceof FloatType || $offsetType instanceof BooleanType) {
			return new IntegerType();

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
			$properties['itemType'],
			$properties['itemTypeInferredFromLiteralArray']
		);
	}

}
