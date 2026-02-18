---
title: "sealed.onEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/**
 * @phpstan-sealed SomeClass
 */
enum Status
{
	case Active;
	case Inactive;
}
```

## Why is it reported?

The `@phpstan-sealed` PHPDoc tag is placed on an enum. This tag restricts which types can extend or implement a given type, but enums in PHP cannot be extended. Enums are implicitly final, so there are no subtypes to restrict. The `@phpstan-sealed` tag is only valid on classes and interfaces.

## How to fix it

Remove the `@phpstan-sealed` tag from the enum:

```diff-php
-/**
- * @phpstan-sealed SomeClass
- */
 enum Status
 {
 	case Active;
 	case Inactive;
 }
```

If sealed type restrictions are needed, use an interface or abstract class instead:

```diff-php
 /**
  * @phpstan-sealed FooHandler|BarHandler
  */
-enum Status
+interface Handler
 {
 }
```
