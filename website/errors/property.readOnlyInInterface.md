---
title: "property.readOnlyInInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	public readonly string $name { get; } // ERROR: Interfaces cannot include readonly hooked properties.
}
```

## Why is it reported?

PHP 8.4 allows interfaces to declare hooked properties, but the `readonly` modifier is not permitted on interface properties. The `readonly` modifier is a concrete implementation detail that should be decided by the implementing class, not enforced by the interface.

This error is not ignorable because it represents a PHP language-level constraint.

## How to fix it

Remove the `readonly` modifier from the interface property declaration:

```diff-php
 <?php declare(strict_types = 1);

 interface HasName
 {
-	public readonly string $name { get; }
+	public string $name { get; }
 }
```

The implementing class can still declare the property as `readonly` if desired:

```php
<?php declare(strict_types = 1);

class User implements HasName
{
	public function __construct(
		public readonly string $name,
	) {}
}
```
