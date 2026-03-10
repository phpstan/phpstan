---
title: "interface.duplicateEnumCase"
shortDescription: "Enum case is declared more than once in an interface body."
ignorable: false
unlikely: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Foo
{
	case Active;
	case Active;
}
```

## Why is it reported?

The same enum case name is declared more than once in an interface body. PHP does not allow redeclaring an enum case within the same type. This is reported by a generic duplicate-declaration rule that checks all class-like structures uniformly. While enum cases inside an interface are not valid PHP, PHPStan still detects the duplicate declaration.

## How to fix it

Remove the duplicate enum case declaration, or rename one of them:

```diff-php
 interface Foo
 {
 	case Active;
-	case Active;
+	case Inactive;
 }
```
