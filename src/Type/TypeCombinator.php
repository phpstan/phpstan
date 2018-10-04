<?php declare(strict_types = 1);

namespace PHPStan\Type;

use PHPStan\Type\Accessory\AccessoryType;
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

		return $fromType;
	}

	public static function removeNull(Type $type): Type
	{
		return self::remove($type, new NullType());
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
		// transform A | (B | C) to A | B | C
		for ($i = 0; $i < count($types); $i++) {
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
			if ($types[$i] instanceof MixedType) {
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
					if ($innerType instanceof AccessoryType) {
						$intermediateAccessoryTypes[] = $innerType;
						continue;
					}
				}

				if ($intermediateArrayType !== null) {
					$arrayTypes[] = $intermediateArrayType;
					$arrayAccessoryTypes = array_merge($arrayAccessoryTypes, $intermediateAccessoryTypes);
					unset($types[$i]);
					continue;
				}
			}
			if (!$types[$i] instanceof ArrayType) {
				continue;
			}

			$arrayTypes[] = $types[$i];
			unset($types[$i]);
		}

		/** @var ArrayType[] $arrayTypes */
		$arrayTypes = $arrayTypes;

		$types = array_values(
			array_merge($types, self::processArrayTypes($arrayTypes, $arrayAccessoryTypes))
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

		// transform A | A to A
		// transform A | never to A
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
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

		if (count($types) === 0) {
			return new NeverType();

		} elseif (count($types) === 1) {
			return $types[0];
		}

		return new UnionType($types);
	}

	/**
	 * @param ArrayType[] $arrayTypes
	 * @param AccessoryType[] $accessoryTypes
	 * @return Type[]
	 */
	private static function processArrayTypes(array $arrayTypes, array $accessoryTypes): array
	{
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
		foreach ($arrayTypes as $arrayType) {
			$arrayIndex = 0;
			foreach ($arrayType->getKeyTypes() as $keyType) {
				$arrayIndex += $constantKeyTypesNumbered[$keyType->getValue()];
			}

			if (!array_key_exists($arrayIndex, $constantArraysBuckets)) {
				$bucket = [];
				foreach ($arrayType->getKeyTypes() as $i => $keyType) {
					$bucket[$keyType->getValue()] = [
						'keyType' => $keyType,
						'valueType' => $arrayType->getValueTypes()[$i],
					];
				}
				$constantArraysBuckets[$arrayIndex] = $bucket;
				continue;
			}

			$bucket = $constantArraysBuckets[$arrayIndex];
			foreach ($arrayType->getKeyTypes() as $i => $keyType) {
				$bucket[$keyType->getValue()]['valueType'] = self::union(
					$bucket[$keyType->getValue()]['valueType'],
					$arrayType->getValueTypes()[$i]
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
