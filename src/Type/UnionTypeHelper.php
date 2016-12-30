<?php declare(strict_types = 1);

namespace PHPStan\Type;

class UnionTypeHelper
{

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @param bool $isNullable
	 * @return string
	 */
	public static function describe(array $types, bool $isNullable): string
	{
		$description = implode('|', array_map(function (Type $type): string {
			return $type->describe();
		}, $types));

		if ($isNullable) {
			$description .= '|null';
		}

		return $description;
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @return bool
	 */
	public static function canAccessProperties(array $types): bool
	{
		foreach ($types as $type) {
			if ($type->canAccessProperties()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @return bool
	 */
	public static function canCallMethods(array $types): bool
	{
		foreach ($types as $type) {
			if ($type->canCallMethods()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $className
	 * @param \PHPStan\Type\Type[] $types
	 * @return \PHPStan\Type\Type[]
	 */
	public static function resolveStatic(string $className, array $types): array
	{
		foreach ($types as $i => $type) {
			if ($type instanceof StaticResolvableType) {
				$types[$i] = $type->resolveStatic($className);
			}
		}

		return $types;
	}

	/**
	 * @param string $className
	 * @param \PHPStan\Type\Type[] $types
	 * @return \PHPStan\Type\Type[]
	 */
	public static function changeBaseClass(string $className, array $types): array
	{
		foreach ($types as $i => $type) {
			if ($type instanceof StaticResolvableType) {
				$types[$i] = $type->changeBaseClass($className);
			}
		}

		return $types;
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @return string|null
	 */
	public static function getClass(array $types)
	{
		$uniqueTypeClass = null;
		foreach ($types as $type) {
			if ($type->getClass() !== null) {
				if ($uniqueTypeClass !== null) {
					return null;
				}

				$uniqueTypeClass = $type->getClass();
			}
		}

		return $uniqueTypeClass;
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @return string[]
	 */
	public static function getReferencedClasses(array $types): array
	{
		$classes = [];
		foreach ($types as $type) {
			$classes = array_merge($classes, $type->getReferencedClasses());
		}

		return $classes;
	}

	/**
	 * @param \PHPStan\Type\UnionType $unionType
	 * @param \PHPStan\Type\Type $type
	 * @return bool|null
	 */
	public static function accepts(UnionType $unionType, Type $type)
	{
		if ($type instanceof UnionType) {
			foreach ($type->getTypes() as $otherOtherType) {
				$matchesAtLeastOne = false;
				foreach ($unionType->getTypes() as $otherType) {
					if ($otherType->accepts($otherOtherType)) {
						$matchesAtLeastOne = true;
						break;
					}
				}
				if (!$matchesAtLeastOne) {
					return false;
				}
			}

			return true;
		}

		return null;
	}

	public static function acceptsAll(Type $type, UnionType $unionType): bool
	{
		foreach ($unionType->getTypes() as $otherType) {
			if (!$type->accepts($otherType)) {
				return false;
			}
		}

		return true;
	}

}
