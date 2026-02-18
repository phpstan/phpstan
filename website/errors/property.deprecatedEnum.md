---
title: "property.deprecatedEnum"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

/** @deprecated Use NewStatus instead */
enum Status
{
	case Active;
	case Inactive;
}

class Order
{
	public Status $status;
}
```

## Why is it reported?

The property's native type declaration references a deprecated enum. Continuing to use deprecated enums couples code to APIs that are planned for removal.

This also triggers when accessing a property (including static properties) of a deprecated enum.

## How to fix it

Replace the deprecated enum with its recommended replacement:

```diff-php
 class Order
 {
-	public Status $status;
+	public NewStatus $status;
 }
```
