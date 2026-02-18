---
title: "enum.missingCase"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Status: string
{
	case Active = 'active';
	case Inactive;
}
```

## Why is it reported?

In a backed enum, every case must have a value. When an enum declares a scalar backing type (such as `string` or `int`), each case is required to provide an explicit value of that type. Unlike pure enums where cases have no values, backed enums require a value assignment for every case.

## How to fix it

Add a value to the case that is missing one:

```diff-php
 enum Status: string
 {
 	case Active = 'active';
-	case Inactive;
+	case Inactive = 'inactive';
 }
```

Alternatively, if the enum should not be backed, remove the scalar type:

```diff-php
-enum Status: string
+enum Status
 {
-	case Active = 'active';
-	case Inactive;
+	case Active;
+	case Inactive;
 }
```
