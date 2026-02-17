---
title: "empty.property"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class Config
{
	public bool $enabled = true;

	public function check(): void
	{
		if (empty($this->enabled)) {
			echo 'Disabled';
		}
	}
}
```

## Why is it reported?

The property used inside `empty()` has a type that PHPStan can fully evaluate for emptiness. When the property type is always falsy or never falsy, the `empty()` check is either always `true` or always `false`, indicating a logic error or a check that should be written more explicitly.

In the example above, `$this->enabled` is typed as `bool`. PHPStan reports this because it can determine the exact behavior of `empty()` on the property's type, and the check should be expressed as an explicit comparison instead of relying on `empty()`.

## How to fix it

Replace `empty()` with an explicit comparison that expresses the intended check:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {
 	public bool $enabled = true;

 	public function check(): void
 	{
-		if (empty($this->enabled)) {
+		if (!$this->enabled) {
 			echo 'Disabled';
 		}
 	}
 }
```

If the property can legitimately be unset or uninitialized, adjust the type to reflect that:

```diff-php
 <?php declare(strict_types = 1);

 class Config
 {
-	public bool $enabled = true;
+	public ?bool $enabled = null;

 	public function check(): void
 	{
-		if (empty($this->enabled)) {
+		if ($this->enabled === null || !$this->enabled) {
 			echo 'Disabled';
 		}
 	}
 }
```
