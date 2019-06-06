<?php declare(strict_types = 1);

namespace PHPStan\Type;

class TypeTraverser
{

	/**
	 * Map a Type recursively
	 *
	 * For every Type instance, the callback can return a new Type, and/or
	 * decide to traverse inner types or to ignore them.
	 *
	 * The following example converts constant strings to objects, while
	 * preserving unions and intersections:
	 *
	 * TypeTraverser::map($type, function (Type $type, callable $traverse): Type {
	 *     if ($type instanceof UnionType || $type instanceof IntersectionType) {
	 *         // Traverse inner types
	 *         return $traverse($type);
	 *     }
	 *     if ($type instanceof ConstantStringType) {
	 *         // Replaces the current type, and don't traverse
	 *         return new ObjectType($type->getValue());
	 *     }
	 *     // Replaces the current type, and don't traverse
	 *     return new MixedType();
	 * });
	 *
	 * @param callable(Type $type, callable(Type): Type $traverse): Type $cb
	 */
	public static function map(Type $type, callable $cb): Type
	{
		$map = static function (Type $type) use ($cb, &$map): Type {
			static $traverse = null;
			if ($traverse === null) {
				$traverse = static function (Type $type) use ($map): Type {
					return $type->traverse($map);
				};
			}
			return $cb($type, $traverse);
		};

		return $map($type);
	}

}
