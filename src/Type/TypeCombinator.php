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
		return self::combine($type, new NullType());
	}

	public static function remove(Type $fromType, Type $typeToRemove): Type
	{
		if ($typeToRemove instanceof UnionType) {
			foreach ($typeToRemove->getTypes() as $unionTypeToRemove) {
				$fromType = self::remove($fromType, $unionTypeToRemove);
			}

			return $fromType;
		}
		$typeToRemoveDescription = $typeToRemove->describe();
		if ($fromType->describe() === $typeToRemoveDescription) {
			return new ErrorType();
		}
		if (
			$fromType instanceof BooleanType
			&& $typeToRemove instanceof TrueOrFalseBooleanType
		) {
			return new ErrorType();
		}
		if (
			$fromType instanceof MixedType
			|| !$fromType instanceof UnionType
		) {
			return $fromType;
		}

		$types = [];
		$iterableTypes = [];
		if ($fromType->isIterable() === TrinaryLogic::YES && !$fromType instanceof ObjectType && !$fromType instanceof StaticType) {
			$iterableTypes[] = $fromType;
		}
		foreach ($fromType->getTypes() as $innerType) {
			if ($innerType->describe() === $typeToRemoveDescription) {
				continue;
			}
			if (
				$innerType instanceof BooleanType
				&& $typeToRemove instanceof TrueOrFalseBooleanType
			) {
				continue;
			}

			if ($innerType->isIterable() === TrinaryLogic::YES && !$innerType instanceof ObjectType && !$innerType instanceof StaticType) {
				$iterableTypes[] = $innerType;
			} else {
				$types[] = $innerType;
			}
		}

		if (count($iterableTypes) === 1) {
			if (count($types) === 0) {
				return new ArrayType($iterableTypes[0]->getItemType());
			}
			return new UnionIterableType($iterableTypes[0]->getItemType(), $types);
		}

		$types = array_merge($types, $iterableTypes);
		if (count($types) > 1) {
			return new CommonUnionType($types);
		} elseif (count($types) === 1) {
			return $types[0];
		}

		throw new \PHPStan\ShouldNotHappenException();
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

	public static function combine(Type ...$typesToCombine): Type
	{
		if (count($typesToCombine) === 1) {
			return $typesToCombine[0];
		}

		$types = [];
		$iterableTypes = [];
		$iterableIterableTypes = [];

		foreach ($typesToCombine as $type) {
			$alreadyAdded = false;
			if ($type instanceof UnionType) {
				$alreadyAdded = true;
				foreach ($type->getTypes() as $innerType) {
					if ($innerType->isIterable() === TrinaryLogic::YES && !$innerType instanceof ObjectType && !$innerType instanceof StaticType) {
						$iterableIterableTypes[$innerType->describe()] = $innerType;
						$iterableTypes[$innerType->getIterableValueType()->describe()] = $innerType->getIterableValueType();
					} else {
						$types[$innerType->describe()] = $innerType;
					}
				}
			}
			if ($type->isIterable() === TrinaryLogic::YES && !$type instanceof ObjectType && !$type instanceof StaticType) {
				$alreadyAdded = true;
				$iterableIterableTypes[$type->describe()] = $type;
				$iterableTypes[$type->getIterableValueType()->describe()] = $type->getIterableValueType();
			}
			if (!$alreadyAdded) {
				$types[$type->describe()] = $type;
			}
		}

		/** @var \PHPStan\Type\Type|null $boolType */
		$boolType = null;
		foreach (['bool', 'true', 'false'] as $boolTypeKey) {
			if (!array_key_exists($boolTypeKey, $types)) {
				continue;
			}
			if ($boolType === null) {
				$boolType = $types[$boolTypeKey];
			} else {
				$boolType = $boolType->combineWith($types[$boolTypeKey]);
			}
			unset($types[$boolTypeKey]);
		}
		if ($boolType !== null) {
			$types[$boolType->describe()] = $boolType;
		}

		if (count($types) === 2 && count($iterableTypes) === 0) {
			if (
				array_key_exists('null', $types)
				&& (
					array_key_exists('mixed', $types)
					|| array_key_exists('void', $types)
				)
			) {
				unset($types['null']);
				$types = array_values($types);
				return $types[0];
			}
		}

		/** @var \PHPStan\Type\Type[] $types */
		$types = array_values($types);
		/** @var \PHPStan\Type\Type[] $iterableTypes */
		$iterableTypes = array_values($iterableTypes);
		/** @var \PHPStan\Type\Type[] $iterableTypes */
		$iterableIterableTypes = array_values($iterableIterableTypes);

		$exactlyOneIterableIterableType = null;
		$otherIterableTypes = [];
		foreach ($iterableIterableTypes as $iterableIterableType) {
			if ($iterableIterableType instanceof IterableIterableType && $iterableIterableType->getIterableValueType() instanceof MixedType) {
				if ($exactlyOneIterableIterableType === null) {
					$exactlyOneIterableIterableType = $iterableIterableType;
				} else {
					$exactlyOneIterableIterableType = null;
					break;
				}
			} else {
				$otherIterableTypes[] = $iterableIterableType;
			}
		}

		if (
			$exactlyOneIterableIterableType !== null
			&& count($otherIterableTypes) > 0
		) {

			$iterableIterableType = new IterableIterableType(
				count($otherIterableTypes) === 1
					? $otherIterableTypes[0]->getIterableValueType()
					: new CommonUnionType(array_map(function (Type $iterableType): Type {
						return $iterableType->getIterableValueType();
					}, $otherIterableTypes))
			);
			if (count($types) === 0) {
				return $iterableIterableType;
			}
			return new CommonUnionType(array_merge($types, [$iterableIterableType]));
		} elseif (count($iterableIterableTypes) === 1) {
			if (count($types) > 0) {
				return new UnionIterableType($iterableIterableTypes[0]->getIterableValueType(), $types);
			}
			return $iterableIterableTypes[0];
		}
		$types = array_merge($types, array_map(function (Type $type): Type {
			return new ArrayType($type);
		}, $iterableTypes));
		if (count($types) > 1) {
			return new CommonUnionType($types);
		}
		if (count($types) === 1) {
			return $types[0];
		}

		throw new \PHPStan\ShouldNotHappenException();
	}

	public static function shouldSkipUnionTypeAccepts(UnionType $unionType): bool
	{
		$typesLimit = self::containsNull($unionType) ? 2 : 1;
		return !self::isUnionTypesEnabled() && count($unionType->getTypes()) > $typesLimit;
	}

}
