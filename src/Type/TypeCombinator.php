<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Accessory\AccessoryType;
use PHPStan\Type\Accessory\NonEmptyArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;

class TypeCombinator
{

	private const CONSTANT_ARRAY_UNION_THRESHOLD = 16;
	private const CONSTANT_SCALAR_UNION_THRESHOLD = 8;

	/** @var bool */
	public static $enableSubtractableTypes = false;

	public static function addNull(Type $type): Type
	{
		return self::union($type, new NullType());
	}

	public static function remove(Type $fromType, Type $typeToRemove): Type
	{
		if ($typeToRemove instanceof UnionType) {
			foreach ($typeToRemove->getTypes() as $unionTypeToRemove) {
				$fromType = self::remove($fromType, $unionTypeToRemove);
			}
			return $fromType;
		}

		if ($fromType instanceof UnionType) {
			$innerTypes = [];
			foreach ($fromType->getTypes() as $innerType) {
				$innerTypes[] = self::remove($innerType, $typeToRemove);
			}

			return self::union(...$innerTypes);
		}

		if ($fromType instanceof BooleanType && $fromType->isSuperTypeOf(new BooleanType())->yes()) {
			if ($typeToRemove instanceof ConstantBooleanType) {
				return new ConstantBooleanType(!$typeToRemove->getValue());
			}
		} elseif ($fromType instanceof IterableType) {
			$traversableType = new ObjectType(\Traversable::class);
			$arrayType = new ArrayType(new MixedType(), new MixedType());
			if ($typeToRemove->isSuperTypeOf($arrayType)->yes()) {
				return $traversableType;
			}
			if ($typeToRemove->isSuperTypeOf($traversableType)->yes()) {
				return $arrayType;
			}
		}

		if ($typeToRemove->isSuperTypeOf($fromType)->yes()) {
			return new NeverType();
		}

		if (
			(new ArrayType(new MixedType(), new MixedType()))->isSuperTypeOf($fromType)->yes()
		) {
			if ($typeToRemove instanceof ConstantArrayType
				&& $typeToRemove->isIterableAtLeastOnce()->no()) {
				return self::intersect($fromType, new NonEmptyArrayType());
			}

			if ($typeToRemove instanceof NonEmptyArrayType) {
				return new ConstantArrayType([], []);
			}
		}

		if (
			self::$enableSubtractableTypes
			&& $fromType instanceof SubtractableType
			&& $fromType->isSuperTypeOf($typeToRemove)->yes()
			&& $typeToRemove->isSuperTypeOf($fromType)->maybe()
		) {
			return $fromType->subtract($typeToRemove);
		}

		return $fromType;
	}

	public static function removeNull(Type $type): Type
	{
		if (self::containsNull($type)) {
			return self::remove($type, new NullType());
		}

		return $type;
	}

	public static function containsNull(Type $type): bool
	{
		if ($type instanceof UnionType) {
			foreach ($type->getTypes() as $innerType) {
				if ($innerType instanceof NullType) {
					return true;
				}
			}

			return false;
		}

		return $type instanceof NullType;
	}

	public static function union(Type ...$types): Type
	{
		$benevolentTypes = [];
		// transform A | (B | C) to A | B | C
		for ($i = 0; $i < count($types); $i++) {
			if ($types[$i] instanceof BenevolentUnionType) {
				foreach ($types[$i]->getTypes() as $benevolentInnerType) {
					$benevolentTypes[$benevolentInnerType->describe(VerbosityLevel::value())] = $benevolentInnerType;
				}
				array_splice($types, $i, 1, $types[$i]->getTypes());
				continue;
			}
			if (!($types[$i] instanceof UnionType)) {
				continue;
			}

			array_splice($types, $i, 1, $types[$i]->getTypes());
		}

		$typesCount = count($types);
		$arrayTypes = [];
		$arrayAccessoryTypes = [];
		$scalarTypes = [];
		$hasGenericScalarTypes = [];
		for ($i = 0; $i < $typesCount; $i++) {
			if ($types[$i] instanceof NeverType) {
				unset($types[$i]);
				continue;
			}
			if (!self::$enableSubtractableTypes && $types[$i] instanceof MixedType) {
				return $types[$i];
			}
			if ($types[$i] instanceof ConstantScalarType) {
				$type = $types[$i];
				$scalarTypes[get_class($type)][md5($type->describe(VerbosityLevel::precise()))] = $type;
				unset($types[$i]);
				continue;
			}
			if ($types[$i] instanceof BooleanType) {
				$hasGenericScalarTypes[ConstantBooleanType::class] = true;
			}
			if ($types[$i] instanceof FloatType) {
				$hasGenericScalarTypes[ConstantFloatType::class] = true;
			}
			if ($types[$i] instanceof IntegerType) {
				$hasGenericScalarTypes[ConstantIntegerType::class] = true;
			}
			if ($types[$i] instanceof StringType) {
				$hasGenericScalarTypes[ConstantStringType::class] = true;
			}
			if ($types[$i] instanceof IntersectionType) {
				$intermediateArrayType = null;
				$intermediateAccessoryTypes = [];
				foreach ($types[$i]->getTypes() as $innerType) {
					if ($innerType instanceof ArrayType) {
						$intermediateArrayType = $innerType;
						continue;
					}
					if ($innerType instanceof AccessoryType || $innerType instanceof CallableType) {
						$intermediateAccessoryTypes[$innerType->describe(VerbosityLevel::precise())] = $innerType;
						continue;
					}
				}

				if ($intermediateArrayType !== null) {
					$arrayTypes[] = $intermediateArrayType;
					$arrayAccessoryTypes[] = $intermediateAccessoryTypes;
					unset($types[$i]);
					continue;
				}
			}
			if (!$types[$i] instanceof ArrayType) {
				continue;
			}

			$arrayTypes[] = $types[$i];
			$arrayAccessoryTypes[] = [];
			unset($types[$i]);
		}

		/** @var ArrayType[] $arrayTypes */
		$arrayTypes = $arrayTypes;

		$arrayAccessoryTypesToProcess = [];
		if (count($arrayAccessoryTypes) > 1) {
			$arrayAccessoryTypesToProcess = array_values(array_intersect_key(...$arrayAccessoryTypes));
		} elseif (count($arrayAccessoryTypes) > 0) {
			$arrayAccessoryTypesToProcess = array_values($arrayAccessoryTypes[0]);
		}

		$types = array_values(
			array_merge(
				$types,
				self::processArrayTypes($arrayTypes, $arrayAccessoryTypesToProcess)
			)
		);

		// simplify string[] | int[] to (string|int)[]
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				if ($types[$i] instanceof IterableType && $types[$j] instanceof IterableType) {
					$types[$i] = new IterableType(
						self::union($types[$i]->getIterableKeyType(), $types[$j]->getIterableKeyType()),
						self::union($types[$i]->getIterableValueType(), $types[$j]->getIterableValueType())
					);
					array_splice($types, $j, 1);
					continue 2;
				}
			}
		}

		foreach ($scalarTypes as $classType => $scalarTypeItems) {
			if (isset($hasGenericScalarTypes[$classType])) {
				continue;
			}
			if ($classType === ConstantBooleanType::class && count($scalarTypeItems) === 2) {
				$types[] = new BooleanType();
				continue;
			}
			foreach ($scalarTypeItems as $type) {
				if (count($scalarTypeItems) > self::CONSTANT_SCALAR_UNION_THRESHOLD) {
					$types[] = $type->generalize();
					break;
				}
				$types[] = $type;
			}
		}

		// transform A | A to A
		// transform A | never to A
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				if (self::$enableSubtractableTypes) {

					if (
						$types[$i] instanceof SubtractableType
						&& $types[$i]->getTypeWithoutSubtractedType()->isSuperTypeOf($types[$j])->yes()
					) {
						$subtractedType = null;
						if ($types[$j] instanceof SubtractableType) {
							$subtractedType = $types[$j]->getSubtractedType();
						}
						$types[$i] = self::intersectWithSubtractedType($types[$i], $subtractedType);
						array_splice($types, $j--, 1);
						continue 1;
					}

					if (
						$types[$j] instanceof SubtractableType
						&& $types[$j]->getTypeWithoutSubtractedType()->isSuperTypeOf($types[$i])->yes()
					) {
						$subtractedType = null;
						if ($types[$i] instanceof SubtractableType) {
							$subtractedType = $types[$i]->getSubtractedType();
						}
						$types[$j] = self::intersectWithSubtractedType($types[$j], $subtractedType);
						array_splice($types, $i--, 1);
						continue 2;
					}
				}

				if (
					!$types[$j] instanceof ConstantArrayType
					&& $types[$j]->isSuperTypeOf($types[$i])->yes()
				) {
					array_splice($types, $i--, 1);
					continue 2;
				}

				if (
					!$types[$i] instanceof ConstantArrayType
					&& $types[$i]->isSuperTypeOf($types[$j])->yes()
				) {
					array_splice($types, $j--, 1);
					continue 1;
				}
			}
		}

		if (count($types) === 0) {
			return new NeverType();

		} elseif (count($types) === 1) {
			return $types[0];
		}

		if (count($benevolentTypes) > 0) {
			$tempTypes = $types;
			foreach ($tempTypes as $i => $type) {
				if (!isset($benevolentTypes[$type->describe(VerbosityLevel::value())])) {
					break;
				}

				unset($tempTypes[$i]);
			}

			if (count($tempTypes) === 0) {
				return new BenevolentUnionType($types);
			}
		}

		return new UnionType($types);
	}

	private static function unionWithSubtractedType(
		Type $type,
		?Type $subtractedType
	): Type
	{
		if ($subtractedType === null) {
			return $type;
		}

		if ($type instanceof SubtractableType) {
			if ($type->getSubtractedType() === null) {
				return $type;
			}

			$subtractedType = self::union(
				$type->getSubtractedType(),
				$subtractedType
			);
			if ($subtractedType instanceof NeverType) {
				$subtractedType = null;
			}

			return $type->changeSubtractedType($subtractedType);
		}

		if ($subtractedType->isSuperTypeOf($type)->yes()) {
			return new NeverType();
		}

		return $type;
	}

	private static function intersectWithSubtractedType(
		SubtractableType $subtractableType,
		?Type $subtractedType
	): Type
	{
		if ($subtractableType->getSubtractedType() === null) {
			return $subtractableType;
		}

		if ($subtractedType === null) {
			return $subtractableType->getTypeWithoutSubtractedType();
		}

		$subtractedType = self::intersect(
			$subtractableType->getSubtractedType(),
			$subtractedType
		);
		if ($subtractedType instanceof NeverType) {
			$subtractedType = null;
		}

		return $subtractableType->changeSubtractedType($subtractedType);
	}

	/**
	 * @param ArrayType[] $arrayTypes
	 * @param Type[] $accessoryTypes
	 * @return Type[]
	 */
	private static function processArrayTypes(array $arrayTypes, array $accessoryTypes): array
	{
		foreach ($arrayTypes as $arrayType) {
			if (!$arrayType instanceof ConstantArrayType) {
				continue;
			}
			if (count($arrayType->getKeyTypes()) > 0) {
				continue;
			}

			foreach ($accessoryTypes as $i => $accessoryType) {
				if (!$accessoryType instanceof NonEmptyArrayType) {
					continue;
				}

				unset($accessoryTypes[$i]);
				break 2;
			}
		}
		if (count($arrayTypes) === 0) {
			return [];
		} elseif (count($arrayTypes) === 1) {
			return [
				self::intersect($arrayTypes[0], ...$accessoryTypes),
			];
		}

		$keyTypesForGeneralArray = [];
		$valueTypesForGeneralArray = [];
		$generalArrayOcurred = false;
		$constantKeyTypesNumbered = [];

		/** @var int|float $nextConstantKeyTypeIndex */
		$nextConstantKeyTypeIndex = 1;

		foreach ($arrayTypes as $arrayType) {
			if (!$arrayType instanceof ConstantArrayType || $generalArrayOcurred) {
				$keyTypesForGeneralArray[] = $arrayType->getKeyType();
				$valueTypesForGeneralArray[] = $arrayType->getItemType();
				$generalArrayOcurred = true;
				continue;
			}

			foreach ($arrayType->getKeyTypes() as $i => $keyType) {
				$keyTypesForGeneralArray[] = $keyType;
				$valueTypesForGeneralArray[] = $arrayType->getValueTypes()[$i];

				$keyTypeValue = $keyType->getValue();
				if (array_key_exists($keyTypeValue, $constantKeyTypesNumbered)) {
					continue;
				}

				$constantKeyTypesNumbered[$keyTypeValue] = $nextConstantKeyTypeIndex;
				$nextConstantKeyTypeIndex *= 2;
				if (!is_int($nextConstantKeyTypeIndex)) {
					$generalArrayOcurred = true;
					continue;
				}
			}
		}

		$createGeneralArray = static function () use ($keyTypesForGeneralArray, $valueTypesForGeneralArray, $accessoryTypes): Type {
			return TypeCombinator::intersect(new ArrayType(
				self::union(...$keyTypesForGeneralArray),
				self::union(...$valueTypesForGeneralArray)
			), ...$accessoryTypes);
		};

		if ($generalArrayOcurred) {
			return [
				$createGeneralArray(),
			];
		}

		/** @var ConstantArrayType[] $arrayTypes */
		$arrayTypes = $arrayTypes;

		/** @var int[] $constantKeyTypesNumbered */
		$constantKeyTypesNumbered = $constantKeyTypesNumbered;

		$constantArraysBuckets = [];
		foreach ($arrayTypes as $arrayTypeAgain) {
			$arrayIndex = 0;
			foreach ($arrayTypeAgain->getKeyTypes() as $keyType) {
				$arrayIndex += $constantKeyTypesNumbered[$keyType->getValue()];
			}

			if (!array_key_exists($arrayIndex, $constantArraysBuckets)) {
				$bucket = [];
				foreach ($arrayTypeAgain->getKeyTypes() as $i => $keyType) {
					$bucket[$keyType->getValue()] = [
						'keyType' => $keyType,
						'valueType' => $arrayTypeAgain->getValueTypes()[$i],
					];
				}
				$constantArraysBuckets[$arrayIndex] = $bucket;
				continue;
			}

			$bucket = $constantArraysBuckets[$arrayIndex];
			foreach ($arrayTypeAgain->getKeyTypes() as $i => $keyType) {
				$bucket[$keyType->getValue()]['valueType'] = self::union(
					$bucket[$keyType->getValue()]['valueType'],
					$arrayTypeAgain->getValueTypes()[$i]
				);
			}

			$constantArraysBuckets[$arrayIndex] = $bucket;
		}

		if (count($constantArraysBuckets) > self::CONSTANT_ARRAY_UNION_THRESHOLD) {
			return [
				$createGeneralArray(),
			];
		}

		$resultArrays = [];
		foreach ($constantArraysBuckets as $bucket) {
			$builder = ConstantArrayTypeBuilder::createEmpty();
			foreach ($bucket as $data) {
				$builder->setOffsetValueType($data['keyType'], $data['valueType']);
			}

			$resultArrays[] = self::intersect($builder->getArray(), ...$accessoryTypes);
		}

		return $resultArrays;
	}

	public static function intersect(Type ...$types): Type
	{
		// transform A & (B | C) to (A & B) | (A & C)
		foreach ($types as $i => $type) {
			if ($type instanceof UnionType) {
				$topLevelUnionSubTypes = [];
				foreach ($type->getTypes() as $innerUnionSubType) {
					$topLevelUnionSubTypes[] = self::intersect(
						$innerUnionSubType,
						...array_slice($types, 0, $i),
						...array_slice($types, $i + 1)
					);
				}

				return self::union(...$topLevelUnionSubTypes);
			}
		}

		// transform A & (B & C) to A & B & C
		foreach ($types as $i => &$type) {
			if (!($type instanceof IntersectionType)) {
				continue;
			}

			array_splice($types, $i, 1, $type->getTypes());
		}

		// transform IntegerType & ConstantIntegerType to ConstantIntegerType
		// transform Child & Parent to Child
		// transform Object & ~null to Object
		// transform A & A to A
		// transform int[] & string to never
		// transform callable & int to never
		// transform A & ~A to never
		// transform int & string to never
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				if (self::$enableSubtractableTypes) {
					if ($types[$j] instanceof SubtractableType) {
						$isSuperTypeSubtractableA = $types[$j]->getTypeWithoutSubtractedType()->isSuperTypeOf($types[$i]);
						if ($isSuperTypeSubtractableA->yes()) {
							$types[$i] = self::unionWithSubtractedType($types[$i], $types[$j]->getSubtractedType());
							array_splice($types, $j--, 1);
							continue 1;
						}
					}

					if ($types[$i] instanceof SubtractableType) {
						$isSuperTypeSubtractableB = $types[$i]->getTypeWithoutSubtractedType()->isSuperTypeOf($types[$j]);
						if ($isSuperTypeSubtractableB->yes()) {
							$types[$j] = self::unionWithSubtractedType($types[$j], $types[$i]->getSubtractedType());
							array_splice($types, $i--, 1);
							continue 2;
						}
					}
				}
				$isSuperTypeA = $types[$j]->isSuperTypeOf($types[$i]);
				if ($isSuperTypeA->no()) {
					return new NeverType();

				} elseif ($isSuperTypeA->yes()) {
					array_splice($types, $j--, 1);
					continue;
				}

				$isSuperTypeB = $types[$i]->isSuperTypeOf($types[$j]);
				if ($isSuperTypeB->maybe()) {
					continue;
				}

				if ($isSuperTypeB->yes()) {
					array_splice($types, $i--, 1);
					continue 2;
				}
			}
		}

		if (count($types) === 1) {
			return $types[0];

		}

		return new IntersectionType($types);
	}

}
