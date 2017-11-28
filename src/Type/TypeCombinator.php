<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypeCombinator
{

	/** @var bool|null */
	private static $unionTypesEnabled;

	public static function setUnionTypesEnabled(bool $enabled)
	{
		if (self::$unionTypesEnabled !== null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		self::$unionTypesEnabled = $enabled;
	}

	public static function isUnionTypesEnabled(): bool
	{
		if (self::$unionTypesEnabled === null) {
			throw new \PHPStan\ShouldNotHappenException();
		}

		return self::$unionTypesEnabled;
	}

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

		if ($fromType instanceof TrueOrFalseBooleanType) {
			if ($typeToRemove instanceof TrueBooleanType) {
				return new FalseBooleanType();
			} elseif ($typeToRemove instanceof FalseBooleanType) {
				return new TrueBooleanType();
			}
		} elseif ($fromType instanceof UnionType) {
			$innerTypes = [];
			foreach ($fromType->getTypes() as $innerType) {
				$innerTypes[] = self::remove($innerType, $typeToRemove);
			}

			return self::union(...$innerTypes);
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
			if ($types[$i] instanceof UnionType) {
				array_splice($types, $i, 1, $types[$i]->getTypes());
			}
		}

		// simplify true | false to bool
		// simplify string[] | int[] to (string|int)[]
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				if ($types[$i] instanceof TrueBooleanType && $types[$j] instanceof FalseBooleanType) {
					$types[$i] = new TrueOrFalseBooleanType();
					array_splice($types, $j, 1);
					continue 2;
				} elseif ($types[$i] instanceof FalseBooleanType && $types[$j] instanceof TrueBooleanType) {
					$types[$i] = new TrueOrFalseBooleanType();
					array_splice($types, $j, 1);
					continue 2;
				} elseif ($types[$i] instanceof ArrayType && $types[$j] instanceof ArrayType) {
					$types[$i] = new ArrayType(
						self::union($types[$i]->getIterableKeyType(), $types[$j]->getIterableKeyType()),
						self::union($types[$i]->getIterableValueType(), $types[$j]->getIterableValueType()),
						$types[$i]->isItemTypeInferredFromLiteralArray() || $types[$j]->isItemTypeInferredFromLiteralArray(),
						$types[$i]->isCallable()->and($types[$j]->isCallable())
					);
					array_splice($types, $j, 1);
					continue 2;
				} elseif ($types[$i] instanceof IterableIterableType && $types[$j] instanceof IterableIterableType) {
					$types[$i] = new IterableIterableType(
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
		// transform true | bool to bool
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				if ($types[$j]->isSuperTypeOf($types[$i])->yes()) {
					array_splice($types, $i--, 1);
					continue 2;

				} elseif ($types[$i]->isSuperTypeOf($types[$j])->yes()) {
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

		return new UnionType($types);
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
			if ($type instanceof IntersectionType) {
				array_splice($types, $i, 1, $type->getTypes());
			}
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

				} elseif ($isSuperTypeB->yes()) {
					array_splice($types, $i--, 1);
					continue 2;
				}
			}
		}

		if (count($types) === 1) {
			return $types[0];

		} else {
			return new IntersectionType($types);
		}
	}

	public static function shouldSkipUnionTypeAccepts(UnionType $unionType): bool
	{
		$typesLimit = self::containsNull($unionType) ? 2 : 1;
		return !self::isUnionTypesEnabled() && count($unionType->getTypes()) > $typesLimit;
	}

}
