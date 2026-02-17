---
title: "class.extendsEnum"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

enum Status
{
	case Active;
	case Inactive;
}

class MyClass extends Status
{
}
```

## Why is it reported?

A class cannot extend an enum. Enums in PHP are a special type and do not support inheritance. Attempting to extend an enum will result in a fatal error at runtime.

In the example above, `MyClass` tries to extend the `Status` enum, which is not allowed.

## How to fix it

Use the enum directly instead of trying to extend it. If additional functionality is needed, use composition:

```diff-php
 <?php declare(strict_types = 1);

-class MyClass extends Status
+class MyClass
 {
+    public function __construct(
+        private Status $status,
+    ) {
+    }
 }
```

Or implement an interface if shared behaviour is needed:

```diff-php
 <?php declare(strict_types = 1);

 interface HasLabel
 {
     public function label(): string;
 }

 enum Status: string implements HasLabel
 {
 	case Active = 'active';
 	case Inactive = 'inactive';

 	public function label(): string
 	{
 		return $this->value;
 	}
 }
```
