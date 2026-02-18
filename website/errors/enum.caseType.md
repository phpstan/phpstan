---
title: "enum.caseType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Status: int
{
	case Active = 'active';
	case Inactive = 2;
}
```

## Why is it reported?

A backed enum declares the type of its case values in the enum declaration (`int` in this example). Every case value must match that type. The value `'active'` is a string, which does not match the declared `int` backing type. PHP will throw a compile-time error for this mismatch.

## How to fix it

Change the case value to match the declared backing type:

```diff-php
 enum Status: int
 {
-	case Active = 'active';
+	case Active = 1;
 	case Inactive = 2;
 }
```

Alternatively, change the backing type if string values are intended:

```diff-php
-enum Status: int
+enum Status: string
 {
 	case Active = 'active';
-	case Inactive = 2;
+	case Inactive = 'inactive';
 }
```
