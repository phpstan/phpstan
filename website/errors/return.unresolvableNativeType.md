---
title: "return.unresolvableNativeType"
ignorable: false
---

## Code example

```php
<?php declare(strict_types = 1);

class Foo
{
}

class Bar
{
}

function doFoo(): Foo&Bar
{
}
```

## Why is it reported?

The native return type declaration contains an intersection type that cannot be resolved to a valid type. This typically happens when the intersection of the specified types results in an impossible type -- for example, intersecting two classes where neither extends the other.

PHP allows writing intersection types in native type declarations starting with PHP 8.1, but PHPStan validates that the resulting type is logically possible.

This error is non-ignorable because it represents a type that can never be satisfied at runtime.

## How to fix it

Fix the return type to use a valid type that can actually exist.

```diff-php
-function doFoo(): Foo&Bar
+function doFoo(): Foo
 {
 }
```

If an intersection type is needed, ensure the types are compatible (e.g., a class and an interface):

```diff-php
-function doFoo(): Foo&Bar
+function doFoo(): Foo&SomeInterface
 {
 }
```
