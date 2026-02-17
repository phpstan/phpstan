---
title: "interface.duplicateConstant"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface StatusInterface
{
	public const ACTIVE = 'active';
	public const ACTIVE = 'enabled';
}
```

## Why is it reported?

The interface declares the same constant name more than once. PHP does not allow redeclaring a constant within the same interface body. This will cause a fatal error at runtime.

## How to fix it

Remove the duplicate constant declaration, or rename one of the constants to avoid the conflict:

```diff-php
 <?php declare(strict_types = 1);

 interface StatusInterface
 {
 	public const ACTIVE = 'active';
-	public const ACTIVE = 'enabled';
+	public const ENABLED = 'enabled';
 }
```
