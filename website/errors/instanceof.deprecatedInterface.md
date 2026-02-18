---
title: "instanceof.deprecatedInterface"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewInterface instead */
interface OldInterface {}

function doFoo(object $obj): void
{
	if ($obj instanceof OldInterface) {
		// ...
	}
}
```

## Why is it reported?

The `instanceof` check references the interface `OldInterface`, which is marked as `@deprecated`. Using deprecated interfaces indicates reliance on code that is slated for removal in a future version.

This rule is provided by the [phpstan-deprecation-rules](https://github.com/phpstan/phpstan-deprecation-rules) extension.

## How to fix it

Replace the deprecated interface with its recommended replacement:

```diff-php
-	if ($obj instanceof OldInterface) {
+	if ($obj instanceof NewInterface) {
 		// ...
 	}
```
