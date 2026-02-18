---
title: "property.promotedNotSupported"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class User
{
	public function __construct(
		public string $name,
		public string $email,
	) {
	}
}
```

## Why is it reported?

The code uses constructor property promotion, which is only available in PHP 8.0 and later. The configured [`phpVersion`](/config-reference#phpversion) is set to a version earlier than PHP 8.0, so this syntax is not supported.

## How to fix it

If upgrading to PHP 8.0+ is not possible, declare properties explicitly and assign them in the constructor body:

```diff-php
 class User
 {
+	public string $name;
+	public string $email;
+
 	public function __construct(
-		public string $name,
-		public string $email,
+		string $name,
+		string $email,
 	) {
+		$this->name = $name;
+		$this->email = $email;
 	}
 }
```

Alternatively, update the [`phpVersion`](/config-reference#phpversion) in the PHPStan configuration to match the actual PHP version in use.
