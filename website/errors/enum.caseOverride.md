---
title: "enum.caseOverride"
shortDescription: "Enum case has the #[Override] attribute but does not override any constant."
ignorable: false
autofixable: true
---

## Code example

```php
<?php declare(strict_types = 1);

enum Suit
{
	#[\Override]
	case Hearts; // ERROR: Enum case Suit::Hearts has #[\Override] attribute but does not override any constant.
	case Spades;
}
```

## Why is it reported?

The `#[\Override]` attribute on an enum case (PHP 8.6+) declares that the case overrides a constant declared in an interface implemented by the enum. If no implemented interface declares a constant with the same name, the attribute is incorrect and PHP throws a fatal error.

This typically happens when the interface constant was renamed or removed, when the enum no longer implements the interface, or when the case name contains a typo.

## How to fix it

If the case is not meant to override an interface constant, remove the attribute:

```diff-php
 enum Suit
 {
-	#[\Override]
 	case Hearts;
 	case Spades;
 }
```

If the case is supposed to override an interface constant, make sure the enum implements the interface and the names match:

```diff-php
-enum Suit
+enum Suit implements HasDefault
 {
 	#[\Override]
-	case Hearts;
+	case DEFAULT;
 	case Spades;
 }
```
