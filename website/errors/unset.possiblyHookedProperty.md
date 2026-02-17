---
title: "unset.possiblyHookedProperty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	public string $name;

	public function reset(): void
	{
		unset($this->name);
	}
}
```

## Why is it reported?

Starting with PHP 8.4, properties can have hooks (get/set). Unsetting a property that is not private, not final, and belongs to a non-final class is reported because a subclass could add hooks to that property. Unsetting a hooked property is not allowed in PHP, so the `unset()` call could fail at runtime if a child class introduces hooks.

## How to fix it

Mark the property or class as `final` to prevent subclasses from adding hooks, or mark the property as `private`.

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
-	public string $name;
+	public final string $name;

 	public function reset(): void
 	{
 		unset($this->name);
 	}
 }
```

Alternatively, mark the entire class as final:

```diff-php
 <?php declare(strict_types = 1);

-class User
+final class User
 {
 	public string $name;

 	public function reset(): void
 	{
 		unset($this->name);
 	}
 }
```
