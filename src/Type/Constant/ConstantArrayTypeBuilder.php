<?php declare(strict_types = 1);

namespace PHPStan\Type\Constant;

use PHPStan\Type\ArrayType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

class ConstantArrayTypeBuilder
{

	/** @var array<int, Type> */
	private $keyTypes = [];

	/** @var array<int, Type> */
	private $valueTypes = [];

	/** @var int */
	private $nextAutoIndex;

	/** @var bool */
	private $degradeToGeneralArray = false;

	/**
	 * @param array<int, ConstantIntegerType|ConstantStringType> $keyTypes
	 * @param array<int, Type> $valueTypes
	 * @param int $nextAutoIndex
	 */
	private function __construct(
		array $keyTypes,
		array $valueTypes,
		int $nextAutoIndex
	)
	{
		$this->keyTypes = $keyTypes;
		$this->valueTypes = $valueTypes;
		$this->nextAutoIndex = $nextAutoIndex;
	}

	public static function createEmpty(): self
	{
		return new self([], [], 0);
	}

	public static function createFromConstantArray(ConstantArrayType $startArrayType): self
	{
		return new self(
			$startArrayType->getKeyTypes(),
			$startArrayType->getValueTypes(),
			$startArrayType->getNextAutoIndex()
		);
	}

	public function setOffsetValueType(?Type $offsetType, Type $valueType): void
	{
		if ($offsetType === null) {
			$offsetType = new ConstantIntegerType($this->nextAutoIndex);
		} else {
			$offsetType = ArrayType::castToArrayKeyType($offsetType);
		}

		if (
			!$this->degradeToGeneralArray
			&& ($offsetType instanceof ConstantIntegerType || $offsetType instanceof ConstantStringType)
		) {
			/** @var ConstantIntegerType|ConstantStringType $keyType */
			foreach ($this->keyTypes as $i => $keyType) {
				if ($keyType->getValue() === $offsetType->getValue()) {
					$this->valueTypes[$i] = $valueType;
					return;
				}
			}

			$this->keyTypes[] = $offsetType;
			$this->valueTypes[] = $valueType;
			$this->nextAutoIndex = $offsetType instanceof ConstantIntegerType
				? max($this->nextAutoIndex, $offsetType->getValue() + 1)
				: $this->nextAutoIndex;
			return;
		}

		$this->keyTypes[] = $offsetType;
		$this->valueTypes[] = $valueType;
		$this->degradeToGeneralArray = true;
	}

	public function getArray(): ArrayType
	{
		if (!$this->degradeToGeneralArray) {
			/** @var array<int, ConstantIntegerType|ConstantStringType> $keyTypes */
			$keyTypes = $this->keyTypes;
			return new ConstantArrayType($keyTypes, $this->valueTypes, $this->nextAutoIndex);
		}

		return new ArrayType(
			TypeCombinator::union(...$this->keyTypes),
			TypeCombinator::union(...$this->valueTypes)
		);
	}

}
