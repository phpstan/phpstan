---
title: "property.hookReadOnly"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
	public readonly int $value { // ERROR: Hooked properties cannot be readonly.
		get => 42;
	}
}
```

## Why is it reported?

PHP does not allow combining property hooks with the `readonly` modifier. This is a language-level restriction since PHP 8.4 introduced property hooks. The `readonly` modifier and property hooks have conflicting semantics -- `readonly` restricts when a property can be written, while hooks allow defining custom get/set behavior that could conflict with the readonly guarantee.

## How to fix it

Remove the `readonly` modifier if the property needs hooks:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public readonly int $value {
+	public int $value {
 		get => 42;
 	}
 }
```

Alternatively, if the property should be readonly, remove the hooks:

```diff-php
 <?php declare(strict_types = 1);

 class Foo
 {
-	public readonly int $value {
-		get => 42;
-	}
+	public readonly int $value;
 }
```
