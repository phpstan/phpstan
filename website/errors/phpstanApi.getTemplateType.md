---
title: "phpstanApi.getTemplateType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

use PHPStan\Type\ObjectType;

class MyTypeExtension
{
	public function getType(): \PHPStan\Type\Type
	{
		$objectType = new ObjectType(\ArrayObject::class);
		return $objectType->getTemplateType(\ArrayObject::class, 'TItem');
	}
}
```

## Why is it reported?

The call to `getTemplateType()` on a PHPStan `Type` object references a template type name that does not exist on the specified class. The class either has no template types at all, or the template type name is misspelled.

In the example above, `ArrayObject` has template types `TKey` and `TValue`, but not `TItem`.

This rule helps PHPStan extension developers catch mistakes when working with generic types in the PHPStan API.

## How to fix it

Use the correct template type name that is defined on the class:

```diff-php
-return $objectType->getTemplateType(\ArrayObject::class, 'TItem');
+return $objectType->getTemplateType(\ArrayObject::class, 'TValue');
```

Check the class's `@template` PHPDoc tags to find the available template type names.
