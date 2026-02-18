---
title: "property.writeOnly"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @property-write int $writeOnlyProperty
 */
#[\AllowDynamicProperties]
class Foo
{
	public function doFoo(): void
	{
		echo $this->writeOnlyProperty;
	}
}
```

## Why is it reported?

The code reads a property that is declared as write-only via the `@property-write` PHPDoc tag. A write-only property is designed to accept values but not to be read. Reading it is not allowed because the class does not guarantee a meaningful value when the property is accessed.

## How to fix it

If the property needs to be both readable and writable, use `@property` instead of `@property-write`:

```diff-php
 /**
- * @property-write int $writeOnlyProperty
+ * @property int $writeOnlyProperty
  */
 #[\AllowDynamicProperties]
 class Foo
```

If the property should remain write-only, restructure the code to avoid reading it. Use a different property or method to expose the value:

```diff-php
 /**
  * @property-write int $writeOnlyProperty
+ * @property-read int $readableProperty
  */
 #[\AllowDynamicProperties]
 class Foo
 {
 	public function doFoo(): void
 	{
-		echo $this->writeOnlyProperty;
+		echo $this->readableProperty;
 	}
 }
```
