---
title: "attribute.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
trait MyAttribute
{
	public function getValue(): string
	{
		return 'value';
	}
}
```

## Why is it reported?

PHP requires attribute classes to be non-abstract classes. A trait cannot be used as an attribute class because PHP needs to instantiate the attribute when it is applied, and traits cannot be instantiated. Using `#[\Attribute]` on a trait is invalid and will cause a runtime error.

In the example above, `MyAttribute` is declared as a trait with the `#[\Attribute]` attribute, which is not allowed.

## How to fix it

Change the trait to a class:

```diff-php
 <?php declare(strict_types = 1);

 #[\Attribute]
-trait MyAttribute
+class MyAttribute
 {
-	public function getValue(): string
+	public function __construct(
+		public readonly string $value = 'value',
+	)
 	{
-		return 'value';
 	}
 }
```
