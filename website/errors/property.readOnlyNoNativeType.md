---
title: "property.readOnlyNoNativeType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Config
{

	/** @var string */
	public readonly $name;

}
```

## Why is it reported?

A `readonly` property must have a native type declaration. PHP requires that all readonly properties have an explicit native type (such as `string`, `int`, `mixed`, etc.). A PHPDoc type alone is not sufficient. This is a language-level requirement enforced by the PHP engine.

## How to fix it

Add a native type declaration to the property:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {

-	/** @var string */
-	public readonly $name;
+	public readonly string $name;

 }
```

If the property can hold multiple types, use a union type or `mixed`:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {

-	/** @var string|int */
-	public readonly $name;
+	public readonly string|int $name;

 }
```
