---
title: "unset.hookedProperty"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	public string $name {
		set(string $value) {
			$this->name = trim($value);
		}
	}

	public function reset(): void
	{
		unset($this->name);
	}
}
```

## Why is it reported?

PHP 8.4 introduced property hooks. A property with hooks cannot be unset because PHP does not support `unset()` on hooked properties. Attempting to do so results in a fatal error at runtime.

In the example above, the `$name` property has a `set` hook, making it a hooked property. Calling `unset($this->name)` on it is not allowed.

## How to fix it

Instead of unsetting the property, assign a default or empty value:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
 	public string $name {
 		set(string $value) {
 			$this->name = trim($value);
 		}
 	}

 	public function reset(): void
 	{
-		unset($this->name);
+		$this->name = '';
 	}
 }
```

If the property should support being absent, consider making it nullable and assigning `null`:

```diff-php
 <?php declare(strict_types = 1);

 class User
 {
-	public string $name {
-		set(string $value) {
+	public ?string $name {
+		set(?string $value) {
 			$this->name = trim($value);
 		}
 	}

 	public function reset(): void
 	{
-		unset($this->name);
+		$this->name = null;
 	}
 }
```
