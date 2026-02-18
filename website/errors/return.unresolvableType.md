---
title: "return.unresolvableType"
ignorable: true
---

## Code example

```php
<?php declare(strict_types = 1);

class ServiceContainer
{
	/**
	 * @return T
	 */
	public function get(string $id): mixed
	{
		return $this->services[$id];
	}
}
```

## Why is it reported?

The PHPDoc `@return` tag contains a type that PHPStan cannot resolve. This typically happens when a template type like `T` is referenced but never declared with a `@template` tag, or when a type alias or class name cannot be found.

In the example above, `T` is used in `@return` but is not declared anywhere as a template type on the class or method.

## How to fix it

Declare the template type with a `@template` tag if you are writing a generic method:

```diff-php
 class ServiceContainer
 {
 	/**
+	 * @template T
+	 * @param class-string<T> $id
 	 * @return T
 	 */
-	public function get(string $id): mixed
+	public function get(string $id): mixed
 	{
 		return $this->services[$id];
 	}
 }
```

Or replace the unresolvable type with a concrete type:

```diff-php
 /**
- * @return T
+ * @return object
  */
 public function get(string $id): mixed
 {
 	return $this->services[$id];
 }
```
