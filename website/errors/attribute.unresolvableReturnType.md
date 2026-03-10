---
title: "attribute.unresolvableReturnType"
shortDescription: "Return type of attribute constructor call contains an unresolvable type after template substitution."
ignorable: true
feasible: false
---

## Code example

```php
<?php declare(strict_types = 1);

interface Loggable {}

/**
 * @template T of object
 */
#[\Attribute]
class MyAttribute
{
	/**
	 * @param T $value
	 * @phpstan-self-out self<T&Loggable>
	 */
	public function __construct(public object $value) {}
}

class PlainObject {}

#[MyAttribute(new PlainObject())]
class Foo {}
```

## Why is it reported?

PHPStan reports this error when the return type of an attribute constructor call contains an unresolvable type after generic template substitution. This can happen when the constructor's resolved return type includes an intersection or conditional type that simplifies to an impossible type.

For example, if a generic attribute's constructor resolves a template type `T` to `PlainObject`, and the return type contains `T&Loggable`, the intersection `PlainObject&Loggable` becomes unresolvable if `PlainObject` does not implement `Loggable`.

## How to fix it

Pass an argument whose type satisfies all constraints in the resolved return type:

```diff-php
-#[MyAttribute(new PlainObject())]
+#[MyAttribute(new LoggableObject())]
 class Foo {}
```

Or simplify the attribute class to avoid intersection return types in the constructor:

```diff-php
 /**
- * @template T of object
+ * @template T of Loggable
  */
 #[\Attribute]
 class MyAttribute
 {
-	/**
-	 * @param T $value
-	 * @phpstan-self-out self<T&Loggable>
-	 */
-	public function __construct(public object $value) {}
+	/** @param T $value */
+	public function __construct(public object $value) {}
 }
```
