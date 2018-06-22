<?php declare(strict_types = 1);

namespace PHPStan\Rules\Properties;

use PHPStan\Reflection\PropertyReflection;

class PropertyDescriptor
{

	/**
	 * @param \PHPStan\Reflection\PropertyReflection $property
	 * @param \PhpParser\Node\Expr\PropertyFetch|\PhpParser\Node\Expr\StaticPropertyFetch $propertyFetch
	 * @return string
	 */
	public function describeProperty(PropertyReflection $property, $propertyFetch): string
	{
		/** @var \PhpParser\Node\Identifier $name */
		$name = $propertyFetch->name;
		if ($propertyFetch instanceof \PhpParser\Node\Expr\PropertyFetch) {
			return sprintf('Property %s::$%s', $property->getDeclaringClass()->getDisplayName(), $name->name);
		}

		return sprintf('Static property %s::$%s', $property->getDeclaringClass()->getDisplayName(), $name->name);
	}

}
