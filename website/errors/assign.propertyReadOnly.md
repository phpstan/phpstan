---
title: "assign.propertyReadOnly"
shortDescription: "Readonly or non-writable property is assigned outside its initialization context."
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @property-read int $readOnlyProperty
 */
class Foo
{
	public function __get(string $name): int
	{
		return 0;
	}

	public function doFoo(): void
	{
		$this->readOnlyProperty = 1;
	}
}
```

## Why is it reported?

A value is being assigned to a property that is declared as read-only via a `@property-read` PHPDoc tag. Such a property is intended only for reading — there is no corresponding `@property-write` or `@property` tag that would allow writes.

In the example above, `$readOnlyProperty` is declared with `@property-read`, so assigning to it inside the class is not allowed.

## How to fix it

Remove the assignment, or if writes are needed, change the PHPDoc tag to `@property` to allow both reading and writing:

```diff-php
-/**
- * @property-read int $readOnlyProperty
- */
+/**
+ * @property int $readOnlyProperty
+ */
 class Foo
 {
```
