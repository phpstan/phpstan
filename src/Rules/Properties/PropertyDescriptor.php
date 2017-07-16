<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Reflection\PropertyReflection;

class PropertyDescriptor
{

	/**
	 * @param \PHPStan\Reflection\PropertyReflection $property
	 * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
	 * @return string|null
	 */
	public function describeProperty(PropertyReflection $property, $propertyFetch)
	{
		if ($propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
			return sprintf('Property %s::$%s', $property->getDeclaringClass()->getDisplayName(), $propertyFetch->name);
		} elseif ($propertyFetch instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
			return sprintf('Static property %s::$%s', $property->getDeclaringClass()->getDisplayName(), $propertyFetch->name);
		}

		return null;
	}

}
