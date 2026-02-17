---
title: "attribute.interface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

#[\Attribute]
interface MyAttribute
{
	public function getValue(): string;
}
```

## Why is it reported?

PHP requires attribute classes to be non-abstract classes. An interface cannot be used as an attribute class because PHP needs to instantiate the attribute when it is applied, and interfaces cannot be instantiated. Using `#[\Attribute]` on an interface is invalid and will cause a runtime error.

In the example above, `MyAttribute` is declared as an interface with the `#[\Attribute]` attribute, which is not allowed.

## How to fix it

Change the interface to a class:

```diff-php
 <?php declare(strict_types = 1);

 #[\Attribute]
-interface MyAttribute
+class MyAttribute
 {
-	public function getValue(): string;
+	public function __construct(
+		public readonly string $value,
+	) {
+	}
 }
```
