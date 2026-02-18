---
title: "property.finalInInterface"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface HasName
{
	final public string $name { get; }
}
```

## Why is it reported?

The property in an interface is declared as `final`. Interfaces define a contract that implementing classes must follow, and all interface properties are inherently abstract. Marking an interface property as `final` contradicts its purpose -- interface members are meant to be implemented, not finalized.

This is a PHP language-level restriction enforced since PHP 8.4 property hooks.

## How to fix it

Remove the `final` keyword from the interface property:

```diff-php
 interface HasName
 {
-	final public string $name { get; }
+	public string $name { get; }
 }
```

If the property should not be overridden by subclasses of the implementing class, apply `final` in the implementing class instead:

```php
<?php declare(strict_types = 1);

interface HasName
{
	public string $name { get; }
}

class User implements HasName
{
	final public string $name {
		get {
			return 'John';
		}
	}
}
```
