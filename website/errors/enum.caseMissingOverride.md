---
title: "enum.caseMissingOverride"
shortDescription: "Enum case overrides an interface constant but is missing the #[Override] attribute."
ignorable: true
autofixable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasDefault
{
	public const DEFAULT = 'default';
}

enum Mode implements HasDefault
{
	case DEFAULT; // ERROR: Enum case Mode::DEFAULT overrides constant HasDefault::DEFAULT but is missing the #[\Override] attribute.
	case Custom;
}
```

## Why is it reported?

An enum case is a class constant of the enum. When an enum implements an interface that declares a constant with the same name, the enum case overrides that constant. Starting with PHP 8.6, the `#[\Override]` attribute can be placed on enum cases to make this intent explicit. If the interface constant is later renamed or removed, PHP reports an error instead of silently keeping an unrelated case.

The check is enabled automatically when [`checkMissingOverrideMethodAttribute`](/config-reference#checkmissingoverridemethodattribute) or [`checkMissingOverridePropertyAttribute`](/config-reference#checkmissingoverridepropertyattribute) is enabled and every analysed PHP version supports `#[\Override]` on class constants (PHP 8.6+, see [`phpVersion`](/config-reference#phpversion)). It can also be turned on or off explicitly with the `checkMissingOverrideConstantAttribute` parameter.

## How to fix it

Add the `#[\Override]` attribute to the enum case:

```diff-php
 enum Mode implements HasDefault
 {
+	#[\Override]
 	case DEFAULT;
 	case Custom;
 }
```
