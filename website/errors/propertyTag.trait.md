---
title: "propertyTag.trait"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

trait LoggerTrait
{
	public function log(string $message): void {}
}

/**
 * @property LoggerTrait $logger
 */
class Foo
{
	use MagicPropertyTrait;
}
```

## Why is it reported?

A `@property` PHPDoc tag references a trait as the property type. Traits cannot be used as types in PHP -- they are not classes or interfaces and cannot be instantiated, type-hinted, or used in `instanceof` checks. A property cannot hold a value of a trait type because no such type exists at runtime.

## How to fix it

Replace the trait with an interface or class that represents the intended type:

```diff-php
 <?php declare(strict_types = 1);

+interface LoggerInterface
+{
+	public function log(string $message): void;
+}
+
 /**
- * @property LoggerTrait $logger
+ * @property LoggerInterface $logger
  */
 class Foo
 {
 	use MagicPropertyTrait;
 }
```

If the trait defines the contract the property should satisfy, extract an interface from the trait and use that interface as the property type instead.
