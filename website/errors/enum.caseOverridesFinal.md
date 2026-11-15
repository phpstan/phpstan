---
title: "enum.caseOverridesFinal"
shortDescription: "Enum case overrides a final constant declared in an implemented interface."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface WithFinal
{
	final public const F = 1;
}

enum OverridesFinal implements WithFinal
{
	case F; // ERROR: Enum case OverridesFinal::F overrides final constant WithFinal::F.
}
```

## Why is it reported?

An enum case is a class constant of the enum. When the enum implements an interface that declares a constant with the same name, the case overrides that constant. Constants declared `final` (PHP 8.1+) cannot be overridden, so PHP throws a fatal error when the enum is declared.

## How to fix it

Rename the enum case so it no longer collides with the final constant:

```diff-php
 enum OverridesFinal implements WithFinal
 {
-	case F;
+	case Foo;
 }
```

If the constant is meant to be overridden, remove the `final` modifier from the interface constant:

```diff-php
 interface WithFinal
 {
-	final public const F = 1;
+	public const F = 1;
 }
```
