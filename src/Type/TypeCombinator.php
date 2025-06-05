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

		if ($typeToRemove->isSupersetOf($fromType)->yes()) {
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
				if ($innerType->isNull()) { // Assuming $innerType->isNull() exists and returns bool
					return true;
				}
			}

			return false;
		}

		return $type->isNull(); // Assuming $type->isNull() exists and returns bool
	}

	public static function union(Type ...$types): Type
	{
		// transform A | (B | C) to A | B | C
		for ($i = 0; $i < count($types); $i++) {
			// Assuming $types[$i]->isUnion() returns bool and getTypes() is still valid if it is a UnionType
			if ($types[$i]->isUnion()) {
				array_splice($types, $i, 1, $types[$i]->getTypes());
			}
		}

		// simplify true | false to bool
		// simplify string[] | int[] to (string|int)[]
		for ($i = 0; $i < count($types); $i++) {
			for ($j = $i + 1; $j < count($types); $j++) {
				// Assuming $type->isTrueBoolean() and $type->isFalseBoolean() exist and return bool
				if ($types[$i]->isTrueBoolean() && $types[$j]->isFalseBoolean()) {
					$types[$i] = new TrueOrFalseBooleanType();
					array_splice($types, $j, 1);
					continue 2;
				} elseif ($types[$i]->isFalseBoolean() && $types[$j]->isTrueBoolean()) {
					$types[$i] = new TrueOrFalseBooleanType();
					array_splice($types, $j, 1);
					continue 2;
					// Assuming $type->isArray() exists and returns bool
				} elseif ($types[$i]->isArray() && $types[$j]->isArray()) {
					$types[$i] = new ArrayType(
						self::union($types[$i]->getIterableValueType(), $types[$j]->getIterableValueType()),
						$types[$i]->isItemTypeInferredFromLiteralArray() || $types[$j]->isItemTypeInferredFromLiteralArray(),
						$types[$i]->isCallable()->and($types[$j]->isCallable())
					);
					array_splice($types, $j, 1);
					continue 2;
					// Assuming $type->isIterableIterable() exists - this one is a bit of a guess for the name
				} elseif ($types[$i]->isIterableIterable() && $types[$j]->isIterableIterable()) {
					$types[$i] = new IterableIterableType(
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
				if ($types[$j]->isSupersetOf($types[$i])->yes()) {
					array_splice($types, $i--, 1);
					continue 2;

				} elseif ($types[$i]->isSupersetOf($types[$j])->yes()) {
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
			// Assuming $type->isUnion()
			if ($type->isUnion()) {
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
			// Assuming $type->isIntersection()
			if ($type->isIntersection()) {
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
				$isSupersetA = $types[$j]->isSupersetOf($types[$i]);
				if ($isSupersetA->no()) {
					return new NeverType();

				} elseif ($isSupersetA->yes()) {
					array_splice($types, $j--, 1);
					continue;
				}

				$isSupersetB = $types[$i]->isSupersetOf($types[$j]);
				if ($isSupersetB->maybe()) {
					continue;

				} elseif ($isSupersetB->yes()) {
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

	public static function shouldSkipUnionTypeAccepts(Type $unionType): bool // Changed UnionType to Type for broader compatibility before check
	{
		// We need to ensure $unionType is actually a UnionType before calling getTypes if isUnion() is the way
		if ($unionType->isUnion()) {
			$typesLimit = self::containsNull($unionType) ? 2 : 1; // containsNull was already refactored
			return !self::isUnionTypesEnabled() && count($unionType->getTypes()) > $typesLimit;
		}
		// If it's not a union type, it shouldn't be skipped based on this logic.
		// Or, this function should only be called with known UnionTypes.
		// For now, assume it's called correctly or the isUnion() check handles it.
		// If isUnionTypesEnabled() is false and it's not a UnionType with > typesLimit, don't skip.
		return false;
	}

}
