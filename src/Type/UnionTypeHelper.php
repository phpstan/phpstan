<?php declare(strict_types = 1);

namespace PHPStan\Type;

class UnionTypeHelper
{

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
	 * @return string[]
	 */
	public static function getReferencedClasses(array $types): array
	{
		$subTypeClasses = [];
		foreach ($types as $type) {
			$subTypeClasses[] = $type->getReferencedClasses();
		}

		return array_merge(...$subTypeClasses);
	}

	/**
	 * @param \PHPStan\Type\Type[] $types
	 * @return \PHPStan\Type\Type[]
	 */
	public static function sortTypes(array $types): array
	{
		usort($types, function (Type $a, Type $b): int {
			if ($a instanceof NullType) {
				return 1;
			} elseif ($b instanceof NullType) {
				return -1;
			}

			return strcasecmp($a->describe(), $b->describe());
		});
		return $types;
	}

}
