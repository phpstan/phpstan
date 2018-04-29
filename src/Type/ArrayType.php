<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptor;
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

	public function describe(VerbosityLevel $level): string
	{
		if ($this->keyType instanceof MixedType) {
			if ($this->itemType instanceof MixedType) {
				return 'array';
			}

			return sprintf('array<%s>', $this->itemType->describe($level));
		}

		return sprintf('array<%s, %s>', $this->keyType->describe($level), $this->itemType->describe($level));
	}

	public function generalizeValues(): self
	{
		$itemType = $this->itemType;
		if ($itemType instanceof ConstantType) {
			$itemType = $itemType->generalize();
		} elseif ($itemType instanceof UnionType) {
			$itemType = TypeCombinator::union(...array_map(function (Type $type): Type {
				if ($type instanceof ConstantType) {
					return $type->generalize();
				}

				return $type;
			}, $itemType->getTypes()));
		}

		return new self($this->keyType, $itemType);
	}

	public function intersectWith(self $otherArray): self
	{
		return new self(
			TypeCombinator::union($this->getKeyType(), $otherArray->getKeyType()),
			TypeCombinator::union($this->getIterableValueType(), $otherArray->getIterableValueType()),
			$this->isItemTypeInferredFromLiteralArray() || $otherArray->isItemTypeInferredFromLiteralArray()
		);
	}

	public function getKeysArray(): self
	{
		return new self(new IntegerType(), $this->keyType, true);
	}

	public function getValuesArray(): self
	{
		return new self(new IntegerType(), $this->itemType, true);
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

	public function getCallableParametersAcceptor(Scope $scope): ParametersAcceptor
	{
		if ($this->isCallable()->no()) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return new TrivialParametersAcceptor();
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

	protected function castToArrayKeyType(Type $offsetType): Type
	{
		if ($offsetType instanceof ConstantScalarType) {
			/** @var int|string $offsetValue */
			$offsetValue = key([$offsetType->getValue() => null]);
			return is_int($offsetValue) ? new ConstantIntegerType($offsetValue) : new ConstantStringType($offsetValue);

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
