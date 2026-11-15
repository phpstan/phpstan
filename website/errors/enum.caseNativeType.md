---
title: "enum.caseNativeType"
shortDescription: "Enum case overrides an interface constant that has a native type."
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface NativeTyped
{
	public const int LIMIT = 1;
}

enum Foo implements NativeTyped
{
	case LIMIT; // ERROR: Enum case Foo::LIMIT is not compatible with native type int of constant NativeTyped::LIMIT.
}
```

## Why is it reported?

An enum case is a class constant of the enum. When the enum implements an interface that declares a constant with the same name, the case overrides that constant. Since PHP 8.3, class constants can have native types, and an overriding constant must declare a compatible native type. An enum case cannot declare a type at all, so PHP rejects it when it overrides a natively typed constant, even when the type would accept the case value, like `mixed` or `UnitEnum`.

## How to fix it

Rename the enum case so it no longer overrides the typed constant:

```diff-php
 enum Foo implements NativeTyped
 {
-	case LIMIT;
+	case Limit;
 }
```

If the interface constant is meant to be overridden by enum cases, remove its native type. A [PHPDoc type](/writing-php-code/phpdoc-types) can still describe the expected value:

```diff-php
 interface NativeTyped
 {
-	public const int LIMIT = 1;
+	/** @var int|\UnitEnum */
+	public const LIMIT = 1;
 }
```
