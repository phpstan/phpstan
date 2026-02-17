---
title: "requireImplements.onTrait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable
{
}

/**
 * @phpstan-require-implements Loggable
 */
class MyClass
{
}
```

## Why is it reported?

The `@phpstan-require-implements` PHPDoc tag is used on a class, interface, or enum instead of a trait. This tag is only valid on traits, where it restricts which classes can use the trait by requiring them to implement a specific interface.

## How to fix it

Move the tag to a trait, or use `implements` directly:

```diff-php
 <?php declare(strict_types = 1);

 interface Loggable
 {
 }

-/**
- * @phpstan-require-implements Loggable
- */
-class MyClass
+class MyClass implements Loggable
 {
 }
```
