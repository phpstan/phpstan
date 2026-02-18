---
title: "property.nativeType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Base
{
	protected int $value;
}

class Child extends Base
{
	protected string $value; // ERROR: Type string of property Child::$value is not the same as type int of overridden property Base::$value.
}
```

## Why is it reported?

When a child class overrides a property from a parent class, the native type of the overriding property must match the native type of the parent property. PHP enforces property type invariance for read-write properties. For properties that are only readable or only writable (via property hooks in PHP 8.4+), covariance or contravariance rules apply respectively.

This error is not ignorable because it represents a PHP language-level constraint that would cause a fatal error at runtime.

## How to fix it

Make the child property's native type match the parent property's native type:

```diff-php
 <?php declare(strict_types = 1);

 class Child extends Base
 {
-	protected string $value;
+	protected int $value;
 }
```

If the child class needs to narrow the type, use a `@var` PHPDoc type annotation while keeping the native type the same:

```diff-php
 <?php declare(strict_types = 1);

 class Child extends Base
 {
-	protected string $value;
+	/** @var positive-int */
+	protected int $value;
 }
```
