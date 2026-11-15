---
title: "classConstant.missingOverride"
shortDescription: "Class constant overrides a parent or interface constant but is missing the #[Override] attribute."
ignorable: true
autofixable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasVersion
{
	public const VERSION = 1;
}

class Foo implements HasVersion
{
	public const VERSION = 2; // ERROR: Constant Foo::VERSION overrides constant HasVersion::VERSION but is missing the #[\Override] attribute.
}
```

## Why is it reported?

Starting with PHP 8.6, the `#[\Override]` attribute can be placed on class constants. It declares that the constant is intended to override a constant from a parent class or an implemented interface. If the parent constant is later renamed or removed, PHP reports an error instead of silently turning the overriding constant into an unrelated new one.

This error is reported when a constant overrides another constant but does not have the attribute. Overriding constants declared in traits are not reported.

The check is enabled automatically when [`checkMissingOverrideMethodAttribute`](/config-reference#checkmissingoverridemethodattribute) or [`checkMissingOverridePropertyAttribute`](/config-reference#checkmissingoverridepropertyattribute) is enabled and every analysed PHP version supports `#[\Override]` on class constants (PHP 8.6+, see [`phpVersion`](/config-reference#phpversion)). It can also be turned on or off explicitly with the `checkMissingOverrideConstantAttribute` parameter.

## How to fix it

Add the `#[\Override]` attribute to the overriding constant:

```diff-php
 class Foo implements HasVersion
 {
+	#[\Override]
 	public const VERSION = 2;
 }
```

When one statement declares several constants (`const A = 1, B = 2;`), the attribute applies to all of them. Split the declaration so that only the overriding constant carries the attribute:

```diff-php
 class Foo implements HasVersion
 {
-	public const VERSION = 2, OTHER = 3;
+	#[\Override]
+	public const VERSION = 2;
+	public const OTHER = 3;
 }
```
