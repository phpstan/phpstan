---
title: "enumImplements.class"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class SomeClass
{
}

enum Status implements SomeClass
{
	case Active;
}
```

## Why is it reported?

An enum uses `implements` to reference a class instead of an interface. In PHP, enums can only implement interfaces. Attempting to implement a class will cause a fatal error.

## How to fix it

Replace the class with an interface:

```diff-php
 <?php declare(strict_types = 1);

-class SomeClass
+interface SomeInterface
 {
 }

-enum Status implements SomeClass
+enum Status implements SomeInterface
 {
 	case Active;
 }
```
