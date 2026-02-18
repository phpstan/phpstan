---
title: "requireExtends.onEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-require-extends SomeClass
 */
enum Status
{
	case Active;
	case Inactive;
}
```

## Why is it reported?

The `@phpstan-require-extends` PHPDoc tag is placed on an enum. This tag is only valid on traits and interfaces. Enums in PHP cannot extend classes, so a `@phpstan-require-extends` constraint on an enum has no meaning and cannot be fulfilled.

## How to fix it

Remove the `@phpstan-require-extends` tag from the enum:

```diff-php
-/**
- * @phpstan-require-extends SomeClass
- */
 enum Status
 {
 	case Active;
 	case Inactive;
 }
```

If the constraint is needed, move it to a trait or interface that the enum can implement:

```diff-php
 /**
  * @phpstan-require-extends SomeClass
  */
-enum Status
+interface StatusInterface
 {
-	case Active;
-	case Inactive;
 }
```
