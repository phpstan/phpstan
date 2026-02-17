---
title: "property.missingOverride"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

abstract class Base
{
	public string $name {
		get => 'base';
	}
}

class Child extends Base
{
	public string $name { // ERROR: Property Child::$name overrides property Base::$name but is missing the #[\Override] attribute.
		get => 'child';
	}
}
```

## Why is it reported?

When a child class overrides a property from a parent class, adding the `#[\Override]` attribute documents the intent and provides safety. If the parent property is later renamed or removed, PHP will report an error, preventing silent bugs where the child property no longer overrides anything.

This check is controlled by the [`checkMissingOverrideMethodAttribute`](/config-reference#checkmissingoverridemethodattribute) configuration option.

## How to fix it

Add the `#[\Override]` attribute to the overriding property:

```diff-php
 <?php declare(strict_types = 1);

 abstract class Base
 {
 	public string $name {
 		get => 'base';
 	}
 }

 class Child extends Base
 {
+	#[\Override]
 	public string $name {
 		get => 'child';
 	}
 }
```
