<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Broker\Broker;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ArrayType;
use PHPStan\Type\CompoundType;
use PHPStan\Type\ConstantType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;

/**
 * @method ConstantIntegerType|ConstantStringType getKeyType()
 */
class ConstantArrayType extends ArrayType implements ConstantType
{

	/** @var (ConstantIntegerType|ConstantStringType)[] */
	private $keyTypes;

	/** @var Type[] */
	private $valueTypes;

	/** @var int */
	private $nextAutoIndex;

	/**
	 * @param (ConstantIntegerType|ConstantStringType)[] $keyTypes
	 * @param Type[] $valueTypes
	 * @param int $nextAutoIndex
	 */
	public function __construct(array $keyTypes, array $valueTypes, int $nextAutoIndex = 0)
	{
		assert(count($keyTypes) === count($valueTypes));

		parent::__construct(
			count($keyTypes) > 0 ? TypeCombinator::union(...$keyTypes) : new MixedType(),
			count($valueTypes) > 0 ? TypeCombinator::union(...$valueTypes) : new MixedType(),
			true
		);

		$this->keyTypes = $keyTypes;
		$this->valueTypes = $valueTypes;
		$this->nextAutoIndex = $nextAutoIndex;
	}

	/**
	 * @return (ConstantIntegerType|ConstantStringType)[]
	 */
	public function getKeyTypes(): array
	{
		return $this->keyTypes;
	}

	/**
	 * @return Type[]
	 */
	public function getValueTypes(): array
	{
		return $this->valueTypes;
	}

	public function accepts(Type $type): bool
	{
		if ($type instanceof self) {
			if (count($this->keyTypes) !== count($type->keyTypes)) {
				return false;
			}

			foreach (array_keys($this->keyTypes) as $i) {
				if (!$this->keyTypes[$i]->accepts($type->keyTypes[$i])) {
					return false;
				}

				if (!$this->valueTypes[$i]->accepts($type->valueTypes[$i])) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	public function isSuperTypeOf(Type $type): TrinaryLogic
	{
		if ($type instanceof self) {
			if (count($this->keyTypes) !== count($type->keyTypes)) {
				return TrinaryLogic::createNo();
			}

			$results = [];
			foreach (array_keys($this->keyTypes) as $i) {
				$results[] = $this->keyTypes[$i]->isSuperTypeOf($type->keyTypes[$i]);
				$results[] = $this->valueTypes[$i]->isSuperTypeOf($type->valueTypes[$i]);
			}

			return TrinaryLogic::createYes()->and(...$results);
		}

		if ($type instanceof ArrayType) {
			return TrinaryLogic::createMaybe()->and(
				$this->getKeyType()->isSuperTypeOf($type->getKeyType()),
				$this->getItemType()->isSuperTypeOf($type->getItemType())
			);
		}

		if ($type instanceof CompoundType) {
			return $type->isSubTypeOf($this);
		}

		return TrinaryLogic::createNo();
	}

	public function isCallable(): TrinaryLogic
	{
		if (count($this->keyTypes) !== 2) {
			return TrinaryLogic::createNo();
		}

		if ($this->keyTypes[0]->isSuperTypeOf(new ConstantIntegerType(0))->no()) {
			return TrinaryLogic::createNo();
		}

		if ($this->keyTypes[1]->isSuperTypeOf(new ConstantIntegerType(1))->no()) {
			return TrinaryLogic::createNo();
		}

		$classOrObject = $this->valueTypes[0];
		$method = $this->valueTypes[1];

		if ($classOrObject instanceof ConstantStringType) {
			$className = $classOrObject->getValue();

		} elseif ($classOrObject instanceof TypeWithClassName) {
			$className = $classOrObject->getClassName();

		} else {
			return TrinaryLogic::createMaybe();
		}

		$broker = Broker::getInstance();
		if (!$broker->hasClass($className)) {
			return TrinaryLogic::createNo();
		}

		if ($method instanceof ConstantStringType) {
			$methodName = $method->getValue();

		} else {
			return TrinaryLogic::createMaybe();
		}

		if (!$broker->getClass($className)->hasMethod($methodName)) {
			return TrinaryLogic::createNo();
		}

		return TrinaryLogic::createYes();
	}


	public function getOffsetValueType(Type $offsetType): Type
	{
		$matchingValueTypes = [];
		foreach ($this->keyTypes as $i => $keyType) {
			if (!$keyType->isSuperTypeOf($offsetType)->no()) {
				$matchingValueTypes[] = $this->valueTypes[$i];
			}
		}

		if (count($matchingValueTypes) > 0) {
			return TypeCombinator::union(...$matchingValueTypes);

		} else {
			return new ErrorType(); // undefined offset
		}
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): Type
	{
		if ($offsetType === null) {
			$offsetType = new ConstantIntegerType($this->nextAutoIndex);

		} else {
			$offsetType = parent::castToArrayKeyType($offsetType);
		}

		if ($offsetType instanceof ConstantIntegerType || $offsetType instanceof ConstantStringType) {
			foreach ($this->keyTypes as $i => $keyType) {
				if ($keyType->getValue() === $offsetType->getValue()) {
					$newValueTypes = $this->valueTypes;
					$newValueTypes[$i] = $valueType;
					return new self($this->keyTypes, $newValueTypes, $this->nextAutoIndex);
				}
			}

			$newKeyTypes = $this->keyTypes;
			$newKeyTypes[] = $offsetType;
			$newValueTypes = $this->valueTypes;
			$newValueTypes[] = $valueType;
			$newNextAutoIndex = $offsetType instanceof ConstantIntegerType
				? max($this->nextAutoIndex, $offsetType->getValue() + 1)
				: $this->nextAutoIndex;

			return new self($newKeyTypes, $newValueTypes, $newNextAutoIndex);
		}

		return parent::setOffsetValueType($offsetType, $valueType);
	}

	public function unsetOffset(Type $offsetType): self
	{
		$offsetType = parent::castToArrayKeyType($offsetType);
		if ($offsetType instanceof ConstantIntegerType || $offsetType instanceof ConstantStringType) {
			foreach ($this->keyTypes as $i => $keyType) {
				if ($keyType->getValue() === $offsetType->getValue()) {
					$newKeyTypes = $this->keyTypes;
					unset($newKeyTypes[$i]);
					$newValueTypes = $this->valueTypes;
					unset($newValueTypes[$i]);
					return new self(array_values($newKeyTypes), array_values($newValueTypes), $this->nextAutoIndex);
				}
			}
		}

		return $this;
	}

	public function generalize(): Type
	{
		return new ArrayType($this->getKeyType(), $this->getItemType(), true);
	}

	public function count(): int
	{
		return count($this->getKeyTypes());
	}

	public static function __set_state(array $properties): Type
	{
		return new self($properties['keyTypes'], $properties['valueTypes'], $properties['nextAutoIndex']);
	}

}
